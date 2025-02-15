<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\Utils;

use Nette;

use function sprintf;
use function strpos;

final class Strings extends Nette\Utils\Strings
{
    public static function snakeCaseToCamelCase(
        string $snakeCase,
    ): string {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $snakeCase))));
    }

    public static function tableNameToEntityName(string $tableName): string
    {
        return ucfirst(self::snakeCaseToCamelCase($tableName));
    }

    public static function withRootNamespace(
        ?string $entity,
    ): ?string {
        if ($entity === null) {
            return null;
        }

        if (strpos($entity, '\\') === 0) {
            return $entity;
        }

        return sprintf('\%s', $entity);
    }
}
