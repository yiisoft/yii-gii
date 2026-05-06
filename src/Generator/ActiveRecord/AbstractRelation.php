<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use function ucfirst;

abstract class AbstractRelation
{
    private ?string $resolvedName = null;

    abstract public function getRelatedModel(): string;

    /**
     * Build link array [foreign_column => local_column]
     *
     * @return array<string, string>
     */
    abstract public function getLink(): array;

    abstract public function getInverseOf(): string;

    /**
     * Computes the default relation name from FK column names and default name.
     * Override this in concrete classes.
     */
    abstract protected function computeName(): string;

    /**
     * Returns the relation name, using a resolved name if one was set via {@see withName()}.
     */
    final public function getName(): string
    {
        return $this->resolvedName ?? $this->computeName();
    }

    /**
     * Returns an unambiguous relation name that does not strip identity suffixes.
     * Used to resolve collisions when multiple FK columns normalize to the same base name.
     */
    abstract public function getUnambiguousName(): string;

    /**
     * Returns a new instance with the given resolved name, used for collision resolution.
     */
    public function withName(string $name): static
    {
        $new = clone $this;
        $new->resolvedName = $name;
        return $new;
    }

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
