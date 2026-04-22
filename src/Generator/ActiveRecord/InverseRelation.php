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
    ) {
    }

    public function getName(): string
    {
        $relatedModel = $this->getRelatedModel();

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
            $link[$columnName] = $this->foreignKey->foreignColumnNames[$index];
        }

        return $link;
    }

    public function getInverseOf(): string
    {
        return lcfirst((new Inflector())->tableToClass($this->foreignKey->foreignTableName));
    }

    /**
     * Returns the method name for the relation query (e.g., "getPostsQuery" or "getProfileQuery").
     */
    public function getQueryMethodName(): string
    {
        return 'get' . $this->getRelatedModel() . 'Query';
    }

    /**
     * Returns the method name for the relation getter (e.g., "getPosts" or "getProfile").
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
