<?php

declare(strict_types=1);

use Krajcik\DataBuilder\BuilderCompiler;
use Krajcik\DataBuilder\Dto\Configuration;
use Krajcik\DataBuilder\Dto\BuilderToGenerateDto;
use Nette\Loaders\RobotLoader;
use Nette\Utils\FileSystem;

require __DIR__ . '/../vendor/autoload.php';

function compile(): void
{
    $targetFolder = __DIR__ . '/../temp/tests/Generated';

    FileSystem::delete(sprintf('%s/%s', $targetFolder, BuilderCompiler::BUILDER_DIR));
    $configuration = new Configuration(
        $targetFolder,
        'localhost:13306',
        'testdb',
        'root',
        'root',
        'Tests\Builder\Generated'
    );

    $compiler = new BuilderCompiler($configuration);

    $builderToGenerate = [
        BuilderToGenerateDto::createFromDoctrineEntity('Api\Contact\Entity\Contact'),
        BuilderToGenerateDto::createFromDoctrineEntity('Api\Contact\Entity\ContactData'),
    ];

    $compiler->compile($builderToGenerate);
}


$loader = new RobotLoader();
$loader
    ->addDirectory(__DIR__ . '/../src/')
    ->addDirectory(__DIR__ . '/../../../blogic/mydock/moduleApi/')
    ->setTempDirectory(__DIR__ . '/../temp/')
    ->register();

set_time_limit(-1);
ini_set('memory_limit', '-1');
error_reporting(E_ALL);

compile();

echo "[DONE]\n";
