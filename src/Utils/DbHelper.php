<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\Utils;

final class DbHelper
{
    /**
     * @param array{name: string}[] $columns
     * @return array{name: string}[]
     */
    public static function sortTableColumns(
        array $columns,
    ): array {
        usort($columns, static function ($firstColumn, $secondColumn) {
            if ($firstColumn['name'] === 'id' && $secondColumn['name'] !== 'id') {
                return -1;
            }

            if ($firstColumn['name'] !== 'id' && $secondColumn['name'] === 'id') {
                return 1;
            }

            return strcmp($firstColumn['name'], $secondColumn['name']);
        });

        return $columns;
    }

    /**
     * @param array{name: string}[] $columns
     */
    public static function hasColumn(array $columns, string $columnName): bool
    {
        foreach ($columns as $column) {
            if ($column['name'] === $columnName) {
                return true;
            }
        }
        return false;
    }
}
