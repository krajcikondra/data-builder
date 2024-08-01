<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\Reflection;
final class ColumnTypeEnum
{
    public const CHAR = 'CHAR';
    public const VARCHAR = 'VARCHAR';
    public const TINYTEXT = 'TINYTEXT';
    public const MEDIUMTEXT = 'MEDIUMTEXT';
    public const TEXT = 'TEXT';
    public const LONGTEXT = 'LONGTEXT';
    public const TINYBLOB = 'TINYBLOB';
    public const MEDIUMBLOB = 'MEDIUMBLOB';
    public const BLOB = 'BLOB';
    public const LONGBLOB = 'LONGBLOB';
    public const SET = 'SET';
    public const ENUM = 'ENUM';
    public const BINARY = 'BINARY';
    public const VARBINARY = 'VARBINARY';
    public const TINYINT = 'TINYINT';
    public const SMALLINT = 'SMALLINT';
    public const MEDIUMINT = 'MEDIUMINT';
    public const INT = 'INT';
    public const BIGINT = 'BIGINT';
    public const DOUBLE = 'DOUBLE';
    public const FLOAT = 'FLOAT';
    public const DECIMAL = 'DECIMAL';
    public const DATE = 'DATE';
    public const TIME = 'TIME';
    public const DATETIME = 'DATETIME';
    public const TIMESTAMP = 'TIMESTAMP';
    public const JSON = 'JSON';

    /**
     * @return string[]
     */
    public static function getDateTypes(): array
    {
        return [
            self::DATE,
        ];
    }

    /**
     * @return string[]
     */
    public static function getDateTimeTypes(): array
    {
        return [
            self::DATETIME,
            self::TIMESTAMP,
        ];
    }

    /**
     * @return string[]
     */
    public static function getEnumTypes(): array
    {
        return [
            self::ENUM,
        ];
    }

    /**
     * @return string[]
     */
    public static function getFloatTypes(): array
    {
        return [
            self::DOUBLE,
            self::FLOAT,
            self::DECIMAL,
        ];
    }

    /**
     * @return string[]
     */
    public static function getIntTypes(): array
    {
        return [
            self::TINYINT,
            self::SMALLINT,
            self::MEDIUMINT,
            self::INT,
            self::BIGINT,
        ];
    }

    /**
     * @return string[]
     */
    public static function getStringTypes(): array
    {
        return [
            self::CHAR,
            self::VARCHAR,
            self::TINYTEXT,
            self::MEDIUMTEXT,
            self::TEXT,
            self::LONGTEXT,
            self::TINYBLOB,
            self::MEDIUMBLOB,
            self::BLOB,
            self::LONGBLOB,
            self::SET,
            self::ENUM,
            self::BINARY,
            self::VARBINARY,
        ];
    }

    /**
     * @return string[]
     */
    public static function getTimeTypes(): array
    {
        return [
            self::TIME,
        ];
    }

    public static function isDateType(
        string $type,
    ): bool {
        return in_array($type, self::getDateTypes(), true);
    }

    public static function isEnumType(
        string $type,
    ): bool {
        return in_array($type, self::getEnumTypes(), true);
    }

    public static function isDateTimeType(
        string $type,
    ): bool {
        return in_array($type, self::getDateTimeTypes(), true);
    }

    public static function isIntType(
        string $type,
    ): bool {
        return in_array($type, self::getIntTypes(), true);
    }

    public static function isFloatType(
        string $type,
    ): bool {
        return in_array($type, self::getFloatTypes(), true);
    }

    public static function isStringType(
        string $type,
    ): bool {
        return in_array($type, self::getStringTypes(), true);
    }

    public static function isJsonType(
        string $type,
    ): bool {
        return $type === self::JSON;
    }

    public static function isTimeType(
        string $type,
    ): bool {
        return in_array($type, self::getTimeTypes(), true);
    }
}
