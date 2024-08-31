<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\Custom;

use Tests\Builder\Generated\BuilderFactory;

abstract class CustomBuilder
{
    public function __construct(protected readonly BuilderFactory $builderFactory)
    {}
}
