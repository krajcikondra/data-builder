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
}
