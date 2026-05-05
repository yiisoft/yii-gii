<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use function lcfirst;

abstract class AbstractRelation
{
    private ?string $uniqueName = null;

    abstract public function getRelatedModel(): string;

    /**
     * Build link array [foreign_column => local_column]
     *
     * @return array<string, string>
     */
    abstract public function getLink(): array;

    abstract public function getInverseOf(): string;

    /**
     * Get the local column names used in the relation link.
     * For outgoing relations (Relation), this returns the FK columns in the current table.
     * For inverse relations (InverseRelation), this returns the referenced columns in the current table.
     *
     * @return list<string>
     */
    abstract public function getLocalColumns(): array;

    public function getName(): string
    {
        if ($this->uniqueName !== null) {
            return $this->uniqueName;
        }

        return lcfirst($this->getRelatedModel());
    }

    /**
     * Set a unique name for this relation to avoid collisions.
     */
    public function setUniqueName(string $name): void
    {
        $this->uniqueName = $name;
    }

    /**
     * Returns the method name for the relation query (e.g., "getProfileQuery").
     */
    public function getQueryMethodName(): string
    {
        return 'get' . $this->getRelatedModel() . 'Query';
    }

    /**
     * Returns the method name for the relation getter (e.g., "getProfile").
     */
    public function getGetterMethodName(): string
    {
        return 'get' . $this->getRelatedModel();
    }

    /**
     * Returns true if this is a hasOne relation.
     */
    public function isHasOne(): bool
    {
        return true;
    }

    /**
     * Returns true if this is a hasMany relation.
     */
    public function isHasMany(): bool
    {
        return false;
    }

    /**
     * Returns the return type for the getter method.
     */
    public function getGetterReturnType(): string
    {
        if ($this->isHasMany()) {
            return 'array';
        }

        return '?' . $this->getRelatedModel();
    }
}
