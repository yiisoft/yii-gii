<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use Yiisoft\Db\Constraint\ForeignKey;
use Yiisoft\Strings\Inflector;

use function lcfirst;

final class Relation
{
    public function __construct(
        private readonly ForeignKey $foreignKey,
        private readonly string $modelName,
    ) {
    }

    public function getName(): string
    {
        return lcfirst($this->getRelatedModel());
    }

    public function getRelatedModel(): string
    {
        $foreignTableName = $this->foreignKey->foreignTableName;
        return (new Inflector())->tableToClass($foreignTableName);
    }

    /**
     * Build link array [foreign_column => local_column]
     *
     * @return array<string, string>
     */
    public function getLink(): array
    {
        $link = [];

        foreach ($this->foreignKey->columnNames as $index => $columnName) {
            $foreignColumnName = $this->foreignKey->foreignColumnNames[$index];
            $link[$foreignColumnName] = $columnName;
        }

        return $link;
    }

    public function getInverseOf(): string
    {
        return lcfirst($this->modelName);
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
