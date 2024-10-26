<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\Loader;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

final class CustomDataBuilderLoader
{
    /**
     * @return string[]
     */
    public function load(string $directory): array
    {
        $classes = [];

        if (!is_dir($directory)) {
            throw new InvalidArgumentException("Directory does not exist: $directory");
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        // Iterate through each file in the directory
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $fileContent = file_get_contents($file->getPathname());

                // Match class definitions using regular expression
                if (preg_match_all('/\bclass\s+(\w+)\b/', $fileContent, $matches)) {
                    preg_match('/\bnamespace\s+(.+);/', $fileContent, $namespaceMatches);
                    foreach ($matches[1] as $className) {
                        $classes[] = sprintf('%s\%s', $namespaceMatches[1], $className);
                    }
                }
            }
        }

        return $this->unfilterAbstract($classes);
    }

    /**
     * @param string[] $classes
     * @return string[]
     */
    private function unfilterAbstract(array $classes): array
    {
        return array_filter($classes, function (string $class): bool {
            $reflection = new ReflectionClass($class);
            return $reflection->isAbstract() === false;
        });
    }
}
