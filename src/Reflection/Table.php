<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\Reflection;

use Iterator;
use Krajcik\DataBuilder\Utils\DbHelper;
use Nette\Database\Context;

/**
 * @implements Iterator<Column>
 */
class Table implements Iterator
{

    protected string $tableName;

    /** @var array<string, mixed> Associative array of has-many references */
    protected array $hasMany;

    /** @var array<string, mixed> Associative array tables */
    protected array $belongsTo;

    /** @var array<string, Column> Associative array of table columns structure. */
    protected array $columns;

    protected string|false $key;

    /** @var array<string>  */
    protected array $keys;

    /**
     * Create instance - get table structure and fill table information properties.
     */
    public function __construct(
        string $tableName,
        Context $db,
    ) {
        $this->tableName = $tableName;
        $this->hasMany = $db->getStructure()->getHasManyReference($tableName);
        $this->belongsTo = $db->getStructure()->getBelongsToReference($tableName);
        $this->addColumns(
            DbHelper::sortTableColumns(
                $db->getStructure()->getColumns($tableName),
            ),
        );
    }

    /**
     * Add given columns to column stack. Extension point.
     * @param array{name: string}[] $columns Array of database column structure.
     */
    protected function addColumns(
        array $columns,
    ): void {
        $this->columns = [];
        foreach ($columns as $column) {
            $this->columns[$column['name']] = new Column($column);
        }
    }

    /**
     * Get name of table that is described.
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Get array of columns.
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Return array of column names.
     * @return string[]
     */
    public function getColumnNames(): array
    {
        return array_keys($this->columns);
    }

    /**
     * Naming shortcut to offsetGet().
     * @see Table::offsetGet()
     */
    public function getColumn(
        string $name,
    ): Column {
        return $this->columns[$name];
    }

    /**
     * Naming shortcut to offsetExists().
     * @see Table::offsetExists()
     */
    public function hasColumn(
        string $name,
    ): bool {
        $this->key = $name;

        return $this->valid();
    }

    /**
     * Get array with referenced table names as keys, referencing columns as values.
     *    Tables related in has-many.
     * @return array<string, mixed>
     */
    public function getHasMany(): array
    {
        return $this->hasMany;
    }

    /**
     * Get array of referencing column name => referenced table name.
     *    Tables related in belongs-to.
     * @return array<string, mixed>
     */
    public function getBelongsTo(): array
    {
        return $this->belongsTo;
    }

    /**
     * Get array of tables related in has-many relation.
     * @return array<string>
     */
    public function getHasManyTables(): array
    {
        return array_keys($this->hasMany);
    }

    /**
     * Get array of tables related in belongs-to relation.
     * @return array<mixed>
     */
    public function getBelongsToTables(): array
    {
        return array_values($this->belongsTo);
    }

    /** Iterator interface */

    /**
     * Reset inner iterator to first key.
     */
    public function rewind(): void
    {
        $this->key = $this->keys[0];
    }

    /**
     * Return inner iterator current item.
     */
    public function current(): Column
    {
        return $this->columns[$this->key];
    }

    /**
     * Return inner iterator current column name.
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * Move inner iterator to next column.
     */
    public function next(): void
    {
        $i = array_search($this->key, $this->keys, true) + 1;
        if (isset($this->keys[$i])) {
            $this->key = $this->keys[$i];
        } else {
            $this->key = false;
        }
    }

    /**
     * Check if current key is valid within columns array.
     */
    public function valid(): bool
    {
        return isset($this->columns[$this->key]);
    }
}
