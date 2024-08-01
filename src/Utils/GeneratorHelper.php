<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\Utils;

use Nette\IOException;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PsrPrinter;
use Nette\Utils\FileSystem as Files;

final class GeneratorHelper
{
    /**
     * Write given class to file.
     * @throws IOException
     */
    public static function writeClassFile(
        ClassType $classType,
        string $path,
    ): void {
        $printer = new PsrPrinter();

        Files::write(
            $path,
            "<?php\n\n"
            . $classType->getNamespace()
            . $printer->printClass($classType, $classType->getNamespace()),
        );
    }
}
