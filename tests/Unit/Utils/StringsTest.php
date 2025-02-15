<?php

declare(strict_types=1);

namespace Unit\Utils;

use Krajcik\DataBuilder\Utils\Strings;
use PHPUnit\Framework\TestCase;

final class StringsTest extends TestCase
{
    public function testSnakeCaseToCamelCase(): void
    {
        $camelCaseString = Strings::snakeCaseToCamelCase('id_creator');
        $this->assertSame('idCreator', $camelCaseString);
    }

    public function testTableNameToEntityName(): void
    {
        $camelCaseString = Strings::tableNameToEntityName('user_person');
        $this->assertSame('UserPerson', $camelCaseString);
    }

}