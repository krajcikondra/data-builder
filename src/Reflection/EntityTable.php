<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\Reflection;

/**
 * Abstract parent of compiled entity table descriptor.
 */
final class EntityTable extends Table
{
    /** Primary key column */
    private ?Column $colPrimary = null;

    /** Inactive column */
    private ?Column $colInactive = null;

    /** Add datetime column */
    private ?Column $colDateAdd = null;

    /** Edit datetime column */
    private ?Column $colDateEdit = null;

    /** Sort column */
    private ?Column $colSort = null;

    /** User who created row */
    private ?Column $colCreator = null;

    /** Company which owns entity */
    private ?Column $colCompany = null;

    /**
     * Add given columns to column stack. Extension point.
     * @param array{name: string}[] $columns Array of database column structure.
     */
    protected function addColumns(
        array $columns,
    ): void {
        $this->columns = [];
        foreach ($columns as $column) {
            $name = $column['name'];
            $refTable = $this->belongsTo[$name] ?? null;
            $col = new EntityColumn($column, $refTable);
            $this->columns[$name] = $col;
            if ($col->isPrimary()) {
                $this->colPrimary = $col;
            }
            if ($col->isInactive()) {
                $this->colInactive = $col;
            }
            if ($col->isDateAdd()) {
                $this->colDateAdd = $col;
            }
            if ($col->isDateEdit()) {
                $this->colDateEdit = $col;
            }
            if ($col->isSort()) {
                $this->colSort = $col;
            }
            if ($col->isCreator()) {
                $this->colCreator = $col;
            }
            if ($col->isCompany()) {
                $this->colCompany = $col;
            }
        }
    }

    /** Special columns getters */

    public function getColPrimary(): ?Column
    {
        return $this->colPrimary;
    }

    public function getColInactive(): ?Column
    {
        return $this->colInactive;
    }

    public function getColDateAdd(): ?Column
    {
        return $this->colDateAdd;
    }

    public function getColDateEdit(): ?Column
    {
        return $this->colDateEdit;
    }

    public function getColSort(): ?Column
    {
        return $this->colSort;
    }

    public function getColCreator(): ?Column
    {
        return $this->colCreator;
    }

    public function getColCompany(): ?Column
    {
        return $this->colCompany;
    }
}
