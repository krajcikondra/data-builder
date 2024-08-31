<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\Dto;

final class Configuration
{

    private string $targetFolder;
    private string $dbHost;
    private string $dbName;
    private string $dbUser;
    private string $dbPassword;

    private string $namespace;
    private ?string $customBuilderFolder;

    public function __construct(
        string $targetFolder,
        string $dbHost,
        string $dbName,
        string $dbUser,
        string $dbPassword,
        string $namespace = 'Tests\Builder\Generated',
        ?string $customBuilderFolder = null,
    )
    {
        $this->targetFolder = $targetFolder;
        $this->dbHost = $dbHost;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->namespace = $namespace;
        $this->customBuilderFolder = $customBuilderFolder;
    }

    public function getTargetFolder(): string
    {
        return $this->targetFolder;
    }

    public function getDbHost(): string
    {
        return $this->dbHost;
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }

    public function getDbUser(): string
    {
        return $this->dbUser;
    }

    public function getDbPassword(): string
    {
        return $this->dbPassword;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getCustomBuilderFolder(): ?string
    {
        return $this->customBuilderFolder;
    }
}
