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
        usort($columns, static function ($a, $b) {
            if ($a['name'] === 'id' && $b['name'] !== 'id') {
                return -1;
            }

            if ($a['name'] !== 'id' && $b['name'] === 'id') {
                return 1;
            }

            return strcmp($a['name'], $b['name']);
        });

        return $columns;
    }

    /**
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    public static function sortByKey(
        array $array,
    ): array {
        uksort(
            $array,
            static fn(string $a, string $b) => $a <=> $b,
        );

        return $array;
    }

    /**
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    public static function sortByKeyWithSecondLayer(
        array $array,
    ): array {
        $arraySubSorted = [];

        foreach ($array as $key => $value) {
            assert(is_string($key));
            if (is_array($value)) {
                $arraySubSorted[$key] = self::sortByKey($value);
            } else {
                $arraySubSorted[$key] = $value;
            }
        }

        return self::sortByKey($arraySubSorted);
    }

    /**
     * @param array<int, string> $array
     * @return array<int, string>
     */
    public static function sortByValue(
        array $array,
    ): array {
        usort(
            $array,
            static fn(string $a, string $b) => $a <=> $b,
        );

        return $array;
    }
}
