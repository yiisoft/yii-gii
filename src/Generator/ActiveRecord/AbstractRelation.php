<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use function ucfirst;

abstract class AbstractRelation
{
    abstract public function getRelatedModel(): string;

    /**
     * Build link array [foreign_column => local_column]
     *
     * @return array<string, string>
     */
    abstract public function getLink(): array;

    abstract public function getInverseOf(): string;

    abstract public function getName(): string;

    /**
     * Returns the method name for the relation getter (e.g., "getProfile").
     */
    public function getGetterMethodName(): string
    {
        return 'get' . ucfirst($this->getName());
    }

    /**
     * Returns the method name for the relation query (e.g., "getProfileQuery").
     */
    public function getQueryMethodName(): string
    {
        return $this->getGetterMethodName() . 'Query';
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
