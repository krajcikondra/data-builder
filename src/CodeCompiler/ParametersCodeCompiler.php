<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\CodeCompiler;

use Krajcik\DataBuilder\Dto\BuilderToGenerateDto;
use Krajcik\DataBuilder\PathResolver;
use Krajcik\DataBuilder\Reflection\EntityColumn;
use Krajcik\DataBuilder\Utils\DbHelper;
use Krajcik\DataBuilder\Utils\PropertyNameResolver;
use Krajcik\DataBuilder\Utils\Strings;
use Krajcik\DataBuilder\Utils\Utils;
use Nette\Database\Context;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

final class ParametersCodeCompiler
{
    private PropertyNameResolver $propertyNameResolver;

    public function __construct(
        private ?Context $dbContext,
        private PathResolver $pathResolver,
    ) {
        $this->propertyNameResolver = new PropertyNameResolver();
    }

    public function precompile(
        BuilderToGenerateDto $data,
    ): ClassType {
        $namespace = new PhpNamespace($this->pathResolver->getBuilderBaseNamespace($data->getClassName()));

        $class = new ClassType($this->pathResolver->createParametersClassName($data->getClassName()), $namespace);
        $class->setFinal();
        $class->addComment(sprintf('Automatically generated by %s.', Strings::withRootNamespace(__CLASS__)));

        return $class;
    }

    public function compile(
        BuilderToGenerateDto $data,
    ): ClassType {
        $class = $this->precompile($data);
        $this->generateProperties($class, $data);
        $this->generateConstructor($class, $data);
        $this->generateGetters($class, $data);

        return $class;
    }

    private function generateProperties(
        ClassType $class,
        BuilderToGenerateDto $data,
    ): void {
        $columns = $this->dbContext->getStructure()->getColumns($data->getTableName());

        foreach (DbHelper::sortTableColumns($columns) as $col) {
            $col = new EntityColumn($col, $data->getTableName());
            $class->addProperty($this->propertyNameResolver->getPropertyName($col, $data))
                ->setPrivate()
                ->setNullable($col->isNullable() || $col->isAutoincrement())
                ->setType(Utils::getType($col, false));
        }
    }

    private function generateConstructor(
        ClassType $class,
        BuilderToGenerateDto $data,
    ): void {
        $method = $class->addMethod('__construct')
            ->setPublic();

        $columns = $this->dbContext->getStructure()->getColumns($data->getTableName());

        foreach (DbHelper::sortTableColumns($columns) as $col) {
            $col = new EntityColumn($col, $data->getTableName());
            $parameterName = $this->propertyNameResolver->getPropertyName($col, $data);
            $method
                ->addParameter($parameterName)
                ->setNullable($col->isNullable() || $col->isAutoincrement())
                ->setType(Utils::getType($col, false));
            $method->addBody(sprintf('$this->%s = $%s;', $parameterName, $parameterName));
        }
    }

    private function generateGetters(
        ClassType $class,
        BuilderToGenerateDto $data,
    ): void {
        $columns = $this->dbContext->getStructure()->getColumns($data->getTableName());

        foreach (DbHelper::sortTableColumns($columns) as $col) {
            $col = new EntityColumn($col, $data->getTableName());
            $parameterName = $this->propertyNameResolver->getPropertyName($col, $data);
            $method = $class->addMethod(sprintf('get%s', Strings::firstUpper($parameterName)))
                ->setPublic()
                ->setReturnType(Utils::getType($col, false))
                ->setReturnNullable($col->isNullable() || $col->isAutoincrement());

            $method->addBody(sprintf('return $this->%s;', $parameterName));
        }
    }
}
