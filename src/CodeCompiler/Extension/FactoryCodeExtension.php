<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\CodeCompiler\Extension;

use Krajcik\DataBuilder\Reflection\EntityColumn;

interface FactoryCodeExtension
{
    public function getDefaultValue(EntityColumn $column): ?string;
}