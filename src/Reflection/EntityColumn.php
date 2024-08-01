<?php

declare(strict_types=1);

namespace Krajcik\DataBuilder\Reflection;

use InvalidArgumentException;

/**
 * General description of database column structure.
 */
final class EntityColumn extends Column
{
    private string $relationColRegexp = '/^id_(.+)$/';
    private const INACTIVE_COL = 'inactive';
    private const DATE_ADD_COL = 'date_add';
    private const DATE_EDIT_COL = 'date_edit';
    private const SORT_COL = 'sort';
    private const CREATOR_COL = 'id_creator';

    private const COMPANY_COL = 'id_company';

    /** @var int Minimal varchar column size to generate textarea */
    private int $minLongStringChars = 129;

    /**
     * Fill column structure.
     * @param array{name: string} $column
     * @param string|null $refTable Referenced table. Has to be given if column is relation. (optional)
     * @throws InvalidArgumentException If relation column without $refTable given.
     */
    public function __construct(
        array $column,
        private ?string $refTable = null,
    ) {
        parent::__construct($column);

        if ($refTable === null && $this->isRelation()) {
            throw new InvalidArgumentException('Relation column given, but $refTable in argument 2 not specified.');
        }
    }

    /**
     * Get name of table the column is related to.
     */
    public function getRelatedTable(): ?string
    {
        if (!$this->isRelation()) {
            return null;
        }

        return $this->refTable;
    }

    /**
     * Get name of relation.
     *    Usually match with related table name, but:
     *        Column id_role linked to table acl_role => relation name is 'role'.
     */
    public function getRelationName(): ?string
    {
        if (!$this->isRelation()) {
            return null;
        }

        if (!preg_match($this->relationColRegexp, $this->getName(), $match)) {
            return null;
        }

        return $match[1];
    }

    public function isRelation(): bool
    {
        return $this->column['vendor']['key'] === 'MUL'
            && preg_match($this->relationColRegexp, $this->getName());
    }

    public function isInactive(): bool
    {
        return $this->isDatetime() && self::INACTIVE_COL === $this->getName();
    }

    public function isDateAdd(): bool
    {
        return $this->isDateOrTime() && self::DATE_ADD_COL === $this->getName();
    }

    public function isDateEdit(): bool
    {
        return $this->isDateOrTime() && self::DATE_EDIT_COL === $this->getName();
    }

    public function isSort(): bool
    {
        return $this->isNumber() && self::SORT_COL === $this->getName();
    }

    public function isCreator(): bool
    {
        return $this->isRelation() && self::CREATOR_COL === $this->getName();
    }

    public function isCompany(): bool
    {
        return $this->isRelation() && self::COMPANY_COL === $this->getName();
    }

    /**
     * Check if string with length lower than $minLongStringChars setting.
     */
    public function isShortString(): bool
    {
        if (!$this->isString()) {
            return false;
        }

        $type = $this->getNativeType();

        return $type === 'CHAR' || ($type === 'VARCHAR' && $this->getSize() < $this->minLongStringChars);
    }

    /**
     * Check if string with length equal or grater than $minLongStringChars setting.
     */
    public function isLongString(): bool
    {
        return $this->isString() && !$this->isShortString();
    }

    /**
     * Check if column isn't nullable nor has any default value.
     */
    public function isRequired(): bool
    {
        return !$this->isNullable();
    }

    /** Setters */

    /**
     * Set minimal character count on which to consider string as long.
     */
    public function setMinLongStringChars(
        int $count,
    ): void {
        $this->minLongStringChars = $count;
    }

    /**
     * Set regular expression to match names of columns with relation to another table.
     */
    public function setRelationColRegexp(
        string $regexp,
    ): void {
        $this->relationColRegexp = $regexp;
    }
}
