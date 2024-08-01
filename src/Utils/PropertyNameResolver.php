<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\Utils;

use Krajcik\DataBuilder\Dto\BuilderToGenerateDto;
use Krajcik\DataBuilder\Reflection\EntityColumn;

final class PropertyNameResolver
{
    public function getPropertyName(EntityColumn $col, BuilderToGenerateDto $builderToGenerateDto): string
    {
        if ($builderToGenerateDto->getColumnToPropertyNames() === null) {
            return Strings::snakeCaseToCamelCase($col->getName());
        }
        $columnToPropertyNames = $builderToGenerateDto->getColumnToPropertyNames();
        if (array_key_exists($col->getName(), $columnToPropertyNames) === false) {
            return Strings::snakeCaseToCamelCase($col->getName());
        }
        return $columnToPropertyNames[$col->getName()];
    }
}