<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use Yiisoft\Db\Constraint\ForeignKey;
use Yiisoft\Strings\Inflector;

final class Relation extends AbstractRelation
{
    public function __construct(
        private readonly ForeignKey $foreignKey,
        private readonly string $modelName,
    ) {}

    protected function computeName(): string
    {
        return ArHelper::getRelationName(
            $this->foreignKey->columnNames,
            $this->foreignKey->foreignTableName,
        );
    }

    public function getUnambiguousName(): string
    {
        return ArHelper::getRelationName(
            $this->foreignKey->columnNames,
            $this->foreignKey->foreignTableName,
            false,
        );
    }

    public function getRelatedModel(): string
    {
        $foreignTableName = $this->foreignKey->foreignTableName;
        return (new Inflector())->tableToClass($foreignTableName);
    }

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
        return ArHelper::getRelationName(
            $this->foreignKey->foreignColumnNames,
            $this->modelName,
        );
    }
}
