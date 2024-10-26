<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder;

use Krajcik\DataBuilder\Dto\Configuration;

final class PathResolver
{
    public function __construct(private Configuration $configuration)
    {
    }

    public function createBuilderClassPath(
        string $entityClass,
    ): string {
        return sprintf(
            '%s/%s/%s.php',
            $this->configuration->getTargetFolder(),
            $entityClass,
            $this->createBuilderClassName($entityClass),
        );
    }

    public function createFactoryClassPath(
        string $entityClass,
    ): string {
        return sprintf(
            '%s/%s/%s.php',
            $this->configuration->getTargetFolder(),
            $entityClass,
            $this->createFactoryClassName($entityClass),
        );
    }

    public function createParametersClassPath(
        string $entityClass,
    ): string {
        return sprintf(
            '%s/%s/%s.php',
            $this->configuration->getTargetFolder(),
            $entityClass,
            $this->createParametersClassName($entityClass),
        );
    }

    public function createBuilderFactoryClassPath(): string
    {
        return sprintf('%s/BuilderFactory.php', $this->configuration->getTargetFolder());
    }

    public function createBuilderClassName(
        string $className,
    ): string {
        return $className . 'Builder';
    }

    public function createFactoryClassName(
        string $className,
    ): string {
        return $className . 'Factory';
    }

    public function createParametersClassName(
        string $className,
    ): string {
        return $className . 'Parameters';
    }

    public function getParameterClassName(
        string $className,
    ): string {
        return sprintf(
            '\\%s\\%s\\%s',
            $this->configuration->getNamespace(),
            $className,
            $this->createParametersClassName($className),
        );
    }

    public function getBuilderBaseNamespace(string $className): string
    {
        return sprintf('%s\%s', $this->configuration->getNamespace(), $className);
    }

    public function getBuilderNamespace(string $className): string
    {
        return sprintf(
            '%s\%s\%s',
            $this->configuration->getNamespace(),
            $className,
            $this->createBuilderClassName($className),
        );
    }

    public function getFactoryNamespace(
        string $className,
        bool $full = true,
    ): string {
        $namespace = sprintf('%s\\%s', $this->configuration->getNamespace(), $className);
        if ($full === false) {
            return $namespace;
        }
        return sprintf('%s\%s', $namespace, $this->createFactoryClassName($className));
    }
}
