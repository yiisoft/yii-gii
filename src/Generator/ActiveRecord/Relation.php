<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use Yiisoft\Db\Schema\TableSchemaInterface;
use Yiisoft\Strings\Inflector;

final class Relation
{
    private ?string $name = null;
    private ?bool $isUniqueForeignColumns = null;

    /**
     * @param string[] $columnNames
     * @param string[] $foreignColumnNames
     */
    public function __construct(
        private readonly TableSchemaInterface $tableSchema,
        private readonly array $columnNames,
        private readonly TableSchemaInterface $foreignTableSchema,
        private readonly array $foreignColumnNames,
    ) {}

    public function getName(): string
    {
        return $this->name ??= ArHelper::getRelationName(
            $this->columnNames,
            $this->foreignTableSchema->getName(),
            $this->isUniqueForeignColumns(),
        );
    }

    public function getInverseOf(): string
    {
        return ArHelper::getRelationName(
            $this->foreignColumnNames,
            $this->tableSchema->getName(),
            $this->isUniqueColumns(),
        );
    }

    public function getRelatedModel(): string
    {
        $foreignTableName = $this->foreignTableSchema->getName();
        return (new Inflector())->tableToClass($foreignTableName);
    }

    /**
     * @return string[]
     */
    public function getLink(): array
    {
        $link = [];

        foreach ($this->columnNames as $index => $columnName) {
            $foreignColumnName = $this->foreignColumnNames[$index];
            $link[$foreignColumnName] = $columnName;
        }

        return $link;
    }

    /**
     * Returns the method name for the relation query (e.g., "getProfileQuery").
     */
    public function getQueryMethodName(): string
    {
        return $this->getGetterMethodName() . 'Query';
    }

    /**
     * Returns the method name for the relation getter (e.g., "getProfile").
     */
    public function getGetterMethodName(): string
    {
        return 'get' . ucfirst($this->getName());
    }

    /**
     * Returns the return type for the getter method.
     */
    public function getGetterReturnType(): string
    {
        if (!$this->isUniqueForeignColumns()) {
            return 'array';
        }

        return '?' . $this->getRelatedModel();
    }

    /**
     * Returns relation method name.
     */
    public function getRelationMethod(): string
    {
        return $this->isUniqueForeignColumns() ? 'hasOne' : 'hasMany';
    }

    private function isUniqueColumns(): bool
    {
        return $this->checkUnique($this->tableSchema, $this->columnNames);
    }

    private function isUniqueForeignColumns(): bool
    {
        return $this->isUniqueForeignColumns ??= $this->checkUnique($this->foreignTableSchema, $this->foreignColumnNames);
    }

    private function checkUnique(TableSchemaInterface $tableSchema, array $columnNames): bool
    {
        sort($columnNames);

        // Check primary key
        $primaryKey = $tableSchema->getPrimaryKey();
        sort($primaryKey);

        if ($columnNames === $primaryKey) {
            return true;
        }

        // Check unique indexes
        foreach ($tableSchema->getUniques() as $index) {
            $indexColumns = $index->columnNames;
            sort($indexColumns);

            if ($columnNames === $indexColumns) {
                return true;
            }
        }

        return false;
    }
}
