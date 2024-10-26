<?php

declare(strict_types=1);

namespace Unit\Utils;

use Krajcik\DataBuilder\Utils\DbHelper;
use PHPUnit\Framework\TestCase;

final class DbHelperTest extends TestCase
{
    public function testSortTableColumns(): void
    {
        $columns = [
            ['name' => 'amount'],
            ['name' => 'count'],
            ['name' => 'id'],
            ['name' => 'id_creator'],
            ['name' => 'updated_at'],
            ['name' => 'created_at'],
        ];
        $sortedColumns = DbHelper::sortTableColumns($columns);
        $this->assertSame([
            ['name' => 'id'],
            ['name' => 'amount'],
            ['name' => 'count'],
            ['name' => 'created_at'],
            ['name' => 'id_creator'],
            ['name' => 'updated_at'],
        ], $sortedColumns);
    }
}