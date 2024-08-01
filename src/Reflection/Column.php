<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\Reflection;

use Nette\SmartObject;


/**
 * General description of database column structure.
 */
class Column
{
    use SmartObject;

    /**
     * @param array<string, mixed> $column
     */
    public function __construct(
        protected array $column,
    ) {
    }

    public function getName(): ?string
    {
        return $this->column['name'];
    }

    public function getTableName(): ?string
    {
        return $this->column['table'];
    }

    public function getSize(): ?int
    {
        return $this->column['size'];
    }

    /**
     * Get MySQL column data-type with length.
     */
    public function getVendorType(): ?string
    {
        return $this->column['vendor']['type'];
    }

    /**
     * Get MySQL column data-type without length.
     */
    public function getNativeType(): ?string
    {
        return $this->column['nativetype'];
    }

    public function getDecimals(): int
    {
        if (
            !$this->isFloat() ||
            !preg_match('/\(([0-9]+),([0-9]+)\)$/', $this->getVendorType(), $match)
        ) {
            return 0;
        }

        return (int) $match[2];
    }

    /**
     * Get column default value.
     * @return mixed|NULL
     */
    public function getDefault()
    {
        return $this->hasDefault() ? $this->column['default'] : null;
    }

    /**
     * Get list of ENUM options.
     * @return string[]
     */
    public function getEnumList(): array
    {
        if ($this->getNativeType() !== 'ENUM') {
            return [];
        }

        $opts = preg_replace('/^.+\((.*)\)$/', '$1', $this->column['vendor']['type']);

        return array_map(static function ($item): string {
            return trim(preg_replace('/^\'(.*)\'$/', '$1', $item) ?? '');
        }, explode(',', $opts));
    }

    public function isNullable(): ?bool
    {
        return $this->column['nullable'];
    }

    public function isUnsigned(): ?bool
    {
        return $this->column['unsigned'];
    }

    /**
     * Check if column has any default value.
     */
    public function hasDefault(): bool
    {
        return isset($this->column['default']) && trim($this->column['default']) !== '';
    }

    public function isPrimary(): bool
    {
        return $this->column['vendor']['key'] === 'PRI';
    }

    /**
     * Check if column has unique key.
     */
    public function isUnique(): bool
    {
        return $this->column['vendor']['key'] === 'UNI';
    }

    public function isMultipleKey(): bool
    {
        return $this->column['vendor']['key'] === 'MUL';
    }

    public function isEnum(): bool
    {
        return ColumnTypeEnum::isEnumType($this->getNativeType());
    }

    public function isBool(): bool
    {
        return $this->getVendorType() === 'tinyint(1)';
    }

    public function isDatetime(): bool
    {
        return ColumnTypeEnum::isDateTimeType($this->getNativeType());
    }

    public function isDate(): bool
    {
        return ColumnTypeEnum::isDateType($this->getNativeType());
    }

    public function isTime(): bool
    {
        return ColumnTypeEnum::isTimeType($this->getNativeType());
    }

    public function isDateOrTime(): bool
    {
        return $this->isDatetime() || $this->isDate() || $this->isTime();
    }

    public function isNumber(): bool
    {
        return $this->isInteger() || $this->isFloat();
    }

    public function isInteger(): bool
    {
        return ColumnTypeEnum::isIntType($this->getNativeType());
    }

    public function isFloat(): bool
    {
        return ColumnTypeEnum::isFloatType($this->getNativeType());
    }

    public function isString(): bool
    {
        return ColumnTypeEnum::isStringType($this->getNativeType());
    }

    public function isJson(): bool
    {
        $nativeType = $this->getNativeType();
        if ($nativeType === null) {
            return false;
        }
        return ColumnTypeEnum::isJsonType($nativeType);
    }
}
