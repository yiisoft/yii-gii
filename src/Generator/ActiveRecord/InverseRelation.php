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
final class InverseRelation extends AbstractRelation
{
    public function __construct(
        private readonly ForeignKey $foreignKey,
        private readonly string $foreignTableName,
    ) {}

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
}
