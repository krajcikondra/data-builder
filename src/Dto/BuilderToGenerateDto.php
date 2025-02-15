<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\Dto;

use Krajcik\DataBuilder\Utils\Strings;

class BuilderToGenerateDto
{
    /**
     * @param array<string, string> $columnToPropertyNames
     */
    protected function __construct(
        private string $tableName,
        private ?string $fullClassName,
        private ?array $columnToPropertyNames = null
    ) {
    }

    public static function create(string $tableName, ?string $fullClassName = null): self
    {
        return new self(
            tableName: $tableName,
            fullClassName: $fullClassName ?? Strings::tableNameToEntityName($tableName),
        );
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getClassName(): string
    {
        $parts = explode('\\', $this->fullClassName);
        return $parts[count($parts) - 1];
    }

    public function getNamespace(): string
    {
        $parts = explode('\\', $this->fullClassName);
        unset($parts[count($parts) - 1]);
        return implode('\\', $parts);
    }

    public function getFullClassName(): ?string
    {
        return $this->fullClassName;
    }

    /**
     * @return string[]|null
     */
    public function getColumnToPropertyNames(): ?array
    {
        return $this->columnToPropertyNames;
    }
}
