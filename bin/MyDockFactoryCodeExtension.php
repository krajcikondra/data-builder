<?php

declare(strict_types=1);

use Krajcik\DataBuilder\CodeCompiler\Extension\FactoryCodeExtension;
use Krajcik\DataBuilder\Reflection\EntityColumn;

final class MyDockFactoryCodeExtension implements FactoryCodeExtension
{

    public function getDefaultValue(EntityColumn $column): ?string
    {
        return match (true) {
            $column->getName() === 'jmeno' => '$this->generator->firstName()',
            $column->getName() === 'prijmeni' => '$this->generator->lastName()',
            $column->getName() === 'mistonarozeni' => '$this->generator->city()',
            $column->getName() === 'datumnarozeni' => '$this->generator->dateTimeBetween("-62 years", "-18 years")',
            $column->getName() === 'vytvoreno' => 'new \DateTime()',
            $column->getName() === 'rodinnystav' => '$this->generator->randomElement(["svobodný", "rozvedený", "ženatý", null])',
            $column->getName() === 'zdrojprijmu' => '$this->generator->randomElement(["osvč", "práce", "Dávky", "Dědictví", null])',
            $column->getName() === 'titulpred' => '$this->generator->randomElement(["Bc.", "Ing.", "Mgr.", null])',
            $column->getName() === 'povolani' => '$this->generator->randomElement(["Zedník", "Programátor", "Elektykář", "Klempíř", "Dělník", "Šéf údržby", "Malíř", null])',
            $column->getName() === 'zdravpoj' => '$this->generator->randomElement(["VZP", "VOZP", "ČPZP", "OZP", "ZP", "ZPMV", null])',
            default => null
        };
    }
}