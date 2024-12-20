<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder;

use Faker\Generator;
use Krajcik\DataBuilder\CodeCompiler\BuilderCodeCompiler;
use Krajcik\DataBuilder\CodeCompiler\Extension\FactoryCodeExtension;
use Krajcik\DataBuilder\CodeCompiler\FactoryCodeCompiler;
use Krajcik\DataBuilder\CodeCompiler\ParametersCodeCompiler;
use Krajcik\DataBuilder\Dto\Configuration;
use Krajcik\DataBuilder\Dto\BuilderToGenerateDto;
use Krajcik\DataBuilder\Loader\CustomDataBuilderLoader;
use Krajcik\DataBuilder\Utils\GeneratorHelper;
use Krajcik\DataBuilder\Utils\Strings;
use Nette\Caching\Storages\MemoryStorage;
use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\Structure;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Nette\Utils\FileSystem;
use Nette\Utils\FileSystem as Files;

class BuilderCompiler
{
    public const BUILDER_DIR = 'Builder';

    protected BuilderCodeCompiler $builderCompiler;
    private FactoryCodeCompiler $factoryCompiler;
    private ParametersCodeCompiler $parametersCompiler;
    protected Context $dbContext;

    protected PathResolver $pathResolver;
    private Configuration $configuration;

    public function __construct(
        Configuration $configuration,
    ) {
        $this->dbContext = $this->initDbContext($configuration);
        $this->pathResolver = new PathResolver($configuration);
        $this->configuration = $configuration;

        Files::createDir($configuration->getTargetFolder());

        $this->builderCompiler = new BuilderCodeCompiler($this->dbContext, $this->pathResolver, $this->configuration);
        $this->parametersCompiler = new ParametersCodeCompiler($this->dbContext, $this->pathResolver);
        $this->factoryCompiler = new FactoryCodeCompiler($this->dbContext, $this->pathResolver, $this->configuration);
    }

    public function setExtension(FactoryCodeExtension $extension): void
    {
        $this->factoryCompiler->setExtension($extension);
    }

    public function clear(): void
    {
        FileSystem::delete(sprintf('%s/%s', $this->configuration->getTargetFolder(), BuilderCompiler::BUILDER_DIR));
    }

    /**
     * @param BuilderToGenerateDto[] $tablesToGenerate
     */
    public function compile(array $tablesToGenerate): void
    {
        $this->clear();
        $builderFactoryClass = $this->createBuilderFactory();
        $builderFactoryConstructor = $builderFactoryClass->addMethod('__construct');
        foreach ($tablesToGenerate as $data) {
            $table = $data->getTableName();
            // Skip already precompiled models
            if (!$this->dbContext->query("SHOW TABLES LIKE ?", $table)->fetchAll()) {
                continue; // skip if table doe`s not exist
            }

            $entityClass = $data->getClassName();

            $class = $this->parametersCompiler->compile($data);
            GeneratorHelper::writeClassFile($class, $this->pathResolver->createParametersClassPath($entityClass));

            $class = $this->builderCompiler->compile($data);
            GeneratorHelper::writeClassFile($class, $this->pathResolver->createBuilderClassPath($entityClass));

            $class = $this->factoryCompiler->compile($data);
            GeneratorHelper::writeClassFile($class, $this->pathResolver->createFactoryClassPath($entityClass));

            $this->generateCreateBuilderMethod($builderFactoryClass, $entityClass);
        }

        $this->generateCreateCustomBuilderMethods($builderFactoryClass);
        $this->generateBuilderFactoryConstructor($builderFactoryConstructor, $builderFactoryClass);


        $this->generateBuilderFactoryFakerGetter($builderFactoryClass);
        GeneratorHelper::writeClassFile($builderFactoryClass, $this->pathResolver->createBuilderFactoryClassPath());
    }

    protected function generateBuilderFactoryConstructor(
        Method $builderFactoryConstructor,
        ClassType $builderFactoryClass,
    ): void {
        $builderFactoryClass
            ->addProperty('db')
            ->setPrivate()
            ->setType(Context::class);

        $builderFactoryConstructor->addParameter('db')->setType(Context::class);
        $builderFactoryConstructor->addBody('$this->db = $db;');
        $builderFactoryConstructor->addBody('$this->faker = null;');
    }

    protected function generateCreateBuilderMethod(ClassType $builderFactoryClass, string $entityClass): Method
    {
        $method = $builderFactoryClass->addMethod(sprintf('create%sBuilder', $entityClass));
        $method->setReturnType($this->pathResolver->getBuilderNamespace($entityClass));
        $method
            ->addParameter('parameters')
            ->setNullable()
            ->setDefaultValue(null)
            ->setType($this->pathResolver->getParameterClassName($entityClass));

        $method->addBody('if ($parameters === null) {');
        $method->addBody(sprintf(
            '    $parameters = (new %s($this->getFaker()))->createDefaultParameters();',
            $this->pathResolver->getFactoryNamespace($entityClass),
        ));
        $method->addBody('}');
        $method->addBody(sprintf(
            'return new %s($parameters, $this->db);',
            $this->pathResolver->getBuilderNamespace($entityClass),
        ));
        return $method;
    }

    protected function generateCreateCustomBuilderMethods(ClassType $builderFactoryClass): void
    {
        if ($this->configuration->getCustomBuilderFolder() === null) {
            return;
        }

        $loader = new CustomDataBuilderLoader();
        $customBuilders = $loader->load($this->configuration->getCustomBuilderFolder());
        foreach ($customBuilders as $customBuilder) {
            $reflectionClass = new \ReflectionClass($customBuilder);
            $method = $builderFactoryClass->addMethod(sprintf('create%s', $reflectionClass->getShortName()));
            $method->setReturnType($reflectionClass->getName());

            $method->addBody(sprintf(
                'return new %s($this);',
                $reflectionClass->getName(),
            ));
        }
    }

    protected function generateCreateCustomBuilderMethod(ClassType $builderFactoryClass, string $entityClass): Method
    {
        $method = $builderFactoryClass->addMethod(sprintf('create%sBuilder', $entityClass));
        $method->setReturnType($this->pathResolver->getBuilderNamespace($entityClass));
        $method
            ->addParameter('parameters')
            ->setNullable()
            ->setDefaultValue(null)
            ->setType($this->pathResolver->getParameterClassName($entityClass));

        $method->addBody('if ($parameters === null) {');
        $method->addBody(sprintf(
            '    $parameters = (new %s($this->getFaker()))->createDefaultParameters();',
            $this->pathResolver->getFactoryNamespace($entityClass),
        ));
        $method->addBody('}');
        $method->addBody(sprintf(
            'return new %s($parameters, $this->db);',
            $this->pathResolver->getBuilderNamespace($entityClass),
        ));
        return $method;
    }

    private function generateBuilderFactoryFakerGetter(
        ClassType $builderFactoryClass,
    ): void {
        $builderFactoryClass
            ->addProperty('faker')
            ->setNullable()
            ->setPrivate()
            ->setType(Generator::class);

        $builderFactoryClass->addMethod('getFaker')
            ->setPrivate()
            ->setReturnType(Generator::class)
            ->addBody('if ($this->faker === null) {')
            ->addBody('    $this->faker = \Faker\Factory::create(\'cs_CZ\');')
            ->addBody('}')
            ->addBody('')
            ->addBody('return $this->faker;');
    }

    private function createBuilderFactory(): ClassType
    {
        $namespace = new PhpNamespace($this->configuration->getNamespace());
        $namespace->addUse('Tests');

        $class = new ClassType('BuilderFactory', $namespace);
        $class->setFinal();
        $class->addComment(sprintf('Automatically generated by %s.', Strings::withRootNamespace(__CLASS__)));

        return $class;
    }
    private function initDbContext(Configuration $config): Context
    {
        $storage = new MemoryStorage();
        $dbConnection = new Connection(
            'mysql:host=' . $config->getDbHost() . ';dbname=' . $config->getDbName(),
            $config->getDbUser(),
            $config->getDbPassword(),
        );

        return new Context($dbConnection, new Structure($dbConnection, $storage));
    }
}
