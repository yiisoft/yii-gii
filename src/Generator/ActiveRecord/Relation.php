<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use Yiisoft\Db\Constraint\ForeignKey;
use Yiisoft\Strings\Inflector;

use function lcfirst;

final class Relation extends AbstractRelation
{
    public function __construct(
        private readonly ForeignKey $foreignKey,
        private readonly string $modelName,
    ) {
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
}
