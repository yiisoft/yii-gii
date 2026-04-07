<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

final class Relation
{
    /**
     * @param array<string, string> $link
     */
    public function __construct(
        public string $name,
        public string $relatedModel,
        public string $type,
        public array $link,
        public ?string $inverseOf = null,
    ) {
    }

    /**
     * Returns the method name for the relation query (e.g., "getProfileQuery").
     */
    public function getQueryMethodName(): string
    {
        return 'get' . ucfirst($this->name) . 'Query';
    }

    /**
     * Returns the method name for the relation getter (e.g., "getProfile").
     */
    public function getGetterMethodName(): string
    {
        return 'get' . ucfirst($this->name);
    }

    /**
     * Returns true if this is a hasOne relation.
     */
    public function isHasOne(): bool
    {
        return $this->type === 'hasOne';
    }

    /**
     * Returns true if this is a hasMany relation.
     */
    public function isHasMany(): bool
    {
        return $this->type === 'hasMany';
    }

    /**
     * Returns the return type for the getter method.
     */
    public function getGetterReturnType(): string
    {
        if ($this->isHasMany()) {
            return 'array';
        }

        return '?' . $this->relatedModel;
    }
}
