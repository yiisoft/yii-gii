<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use Yiisoft\Db\Constraint\ForeignKey;
use Yiisoft\Strings\Inflector;

use function lcfirst;

/**
 * Represents an inverse relation (incoming foreign key from another table).
 *
 * For example, if a Post table has a FK to User, then User has an inverse hasMany relation to Post.
 */
final class InverseRelation
{
    public function __construct(
        private readonly ForeignKey $foreignKey,
        private readonly string $foreignTableName,
        private readonly string $modelName,
        private readonly bool $isUnique,
    ) {
    }

    public function getName(): string
    {
        $relatedModel = $this->getRelatedModel();

        // For hasMany relations, pluralize the name
        if ($this->isHasMany()) {
            $inflector = new Inflector();
            return lcfirst($inflector->toPlural($relatedModel));
        }

        return lcfirst($relatedModel);
    }

    public function getRelatedModel(): string
    {
        return (new Inflector())->tableToClass($this->foreignTableName);
    }

    /**
     * Build link array [local_column => foreign_column]
     * For inverse relations, the link is reversed from the FK direction.
     *
     * @return array<string, string>
     */
    public function getLink(): array
    {
        $link = [];

        foreach ($this->foreignKey->columnNames as $index => $columnName) {
            $foreignColumnName = $this->foreignKey->foreignColumnNames[$index] ?? 'id';
            // Inverse link: map our column to their FK column
            $link[$columnName] = $foreignColumnName;
        }

        return $link;
    }

    public function getInverseOf(): string
    {
        return lcfirst($this->modelName);
    }

    /**
     * Returns the method name for the relation query (e.g., "getPostsQuery" or "getProfileQuery").
     */
    public function getQueryMethodName(): string
    {
        $relatedModel = $this->getRelatedModel();

        if ($this->isHasMany()) {
            $inflector = new Inflector();
            $relatedModel = $inflector->toPlural($relatedModel);
        }

        return 'get' . $relatedModel . 'Query';
    }

    /**
     * Returns the method name for the relation getter (e.g., "getPosts" or "getProfile").
     */
    public function getGetterMethodName(): string
    {
        $relatedModel = $this->getRelatedModel();

        if ($this->isHasMany()) {
            $inflector = new Inflector();
            $relatedModel = $inflector->toPlural($relatedModel);
        }

        return 'get' . $relatedModel;
    }

    /**
     * Returns true if this is a hasOne relation.
     */
    public function isHasOne(): bool
    {
        return $this->isUnique;
    }

    /**
     * Returns true if this is a hasMany relation.
     */
    public function isHasMany(): bool
    {
        return !$this->isUnique;
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
