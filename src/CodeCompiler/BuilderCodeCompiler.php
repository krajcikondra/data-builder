<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\CodeCompiler;

use Krajcik\DataBuilder\Dto\BuilderToGenerateDto;
use Krajcik\DataBuilder\Dto\Configuration;
use Krajcik\DataBuilder\PathResolver;
use Krajcik\DataBuilder\Reflection\EntityColumn;
use Krajcik\DataBuilder\Utils\DbHelper;
use Krajcik\DataBuilder\Utils\PropertyNameResolver;
use Krajcik\DataBuilder\Utils\Strings;
use Krajcik\DataBuilder\Utils\Utils;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;

class BuilderCodeCompiler
{
    private PropertyNameResolver $propertyNameResolver;

    public function __construct(
        protected ?Context $dbContext,
        protected PathResolver $pathResolver,
        protected Configuration $config,
    ) {
        $this->propertyNameResolver = new PropertyNameResolver();
    }

    public function precompile(
        BuilderToGenerateDto $data,
    ): ClassType {
        $namespace = $this->precompileNamespace($data);
        $class = new ClassType($this->pathResolver->createBuilderClassName($data->getClassName()), $namespace);
        $class->setFinal();
        $class->addComment(sprintf('Automatically generated by %s.', Strings::withRootNamespace(__CLASS__)));

        $class->addProperty('parameters')
            ->setPrivate()
            ->setType($this->pathResolver->getParameterClassName($data->getClassName()));

        $class->addProperty('db')
            ->setPrivate()
            ->setType(Context::class);

        return $class;
    }

    protected function precompileNamespace(BuilderToGenerateDto $data): PhpNamespace
    {
        $namespace = new PhpNamespace(sprintf('%s\%s', $this->config->getNamespace(), $data->getClassName()));
        $namespace->addUse($data->getFullClassName());
        $namespace->addUse(Context::class);
        return $namespace;
    }

    public function compile(
        BuilderToGenerateDto $data
    ): ClassType {
        $class = $this->precompile($data);

        $this->createConstructMethod($class, $data);
        $this->createGetDataMethod($class, $data);
        $this->createBuildAndSaveMethod($class, $data);
        $this->createWithMethods($class, $data);

        return $class;
    }

    protected function createConstructMethod(
        ClassType $class,
        BuilderToGenerateDto $data,
    ): Method {
        $parameterType = $this->pathResolver->getParameterClassName($data->getClassName());
        $method = $class->addMethod('__construct')
            ->setPublic();

        $method
            ->addParameter('parameters')
            ->setType($parameterType);

        $method
            ->addParameter('db')
            ->setType(Context::class);

        $method->addBody('$this->parameters = $parameters;');
        $method->addBody('$this->db = $db;');
        return $method;
    }

    private function createGetDataMethod(
        ClassType $class,
        BuilderToGenerateDto $data,
    ): void {
        $method = $class->addMethod('getData')
            ->setPrivate()
            ->setReturnType('array')
            ->addComment('@return array<string, mixed>');

        $columns = $this->dbContext->getStructure()->getColumns($data->getTableName());

        $method->addBody('        $data = [');

        // add referenced columns
        foreach (DbHelper::sortTableColumns($columns) as $col) {
            $col = new EntityColumn($col, $data->getTableName());
            if ($col->getName() === 'id') {
                continue;
            }

            $method->addBody(sprintf(
                '    \'%s\' => $this->parameters->get%s(),',
                $col->getName(),
                Strings::firstUpper($this->propertyNameResolver->getPropertyName($col, $data)),
            ));
        }
        $method->addBody('];');
        $method->addBody('');
        $method->addBody('if ($this->parameters->getId() !== null) {');
        $method->addBody('    $data[\'id\'] = $this->parameters->getId();');
        $method->addBody('}');
        $method->addBody('');
        $method->addBody('return $data;');
    }

    protected function createBuildAndSaveMethod(
        ClassType $class,
        BuilderToGenerateDto $data,
    ): Method {
        $method = $class->addMethod('buildAndSave')
            ->setPublic()
            ->setReturnType(ActiveRow::class);
        $method->addBody(sprintf('return $this->db->table("%s")->insert($this->getData());', $data->getTableName()));
        return $method;
    }


    private function createWithMethods(
        ClassType $class,
        BuilderToGenerateDto $data,
    ): void {
        $columns = $this->dbContext->getStructure()->getColumns($data->getTableName());

        // add referenced columns
        foreach (DbHelper::sortTableColumns($columns) as $col) {
            $col = new EntityColumn($col, $data->getTableName());
            $snakeCaseName = $this->propertyNameResolver->getPropertyName($col, $data);
            $method = $class->addMethod(sprintf('with%s', Strings::firstUpper($snakeCaseName)));
            $method
                ->setPublic()
                ->setReturnType('self')
                ->addParameter($snakeCaseName)
                    ->setType(Utils::getType($col, false))
                    ->setNullable($col->isNullable());

            $method->addBody(sprintf('        return new self('));
            $method->addBody(sprintf('    new %sParameters(', $data->getClassName()));
            foreach (DbHelper::sortTableColumns($columns) as $innerCol) {
                $innerCol = new EntityColumn($innerCol, $data->getTableName());
                if ($innerCol->getName() === $col->getName()) {
                    $columnName = $this->propertyNameResolver->getPropertyName($col, $data);
                    $method->addBody(sprintf(
                        '        %s: $%s,',
                        $columnName,
                        $columnName,
                    ));
                } else {
                    $columnName = $this->propertyNameResolver->getPropertyName($innerCol, $data);
                    $method->addBody(sprintf(
                        '        %s: $this->parameters->get%s(),',
                        $columnName,
                        Strings::firstUpper($columnName),
                    ));
                }
            }
            $method->addBody('    ),');
            $method->addBody('    $this->em,');
            $method->addBody(');');
        }
    }
}
