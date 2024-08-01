<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\Utils;

use Krajcik\DataBuilder\Reflection\EntityColumn;

final class Utils
{
    public static function getType(
        EntityColumn $col,
        bool $withNullable,
    ): string {
        if ($col->isBool()) {
            $type = 'bool';
        } elseif ($col->isInteger()) {
            $type = 'int';
        } elseif ($col->isFloat()) {
            $type = 'float';
        } elseif ($col->isTime()) {
            $type = '\DateTimeInterface';
        } elseif ($col->isDateOrTime()) {
            $type = '\DateTimeInterface';
        } else {
            $type = 'string';
        }

        if ($withNullable === true && $col->isNullable()) {
            $type = sprintf('?%s', $type);
        }

        return $type;
    }
}
