<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use Yiisoft\Db\Constraint\ForeignKey;
use Yiisoft\Strings\Inflector;

/**
 * Represents an inverse relation (incoming foreign key from another table).
 *
 * For example, if a Post table has a FK to User, then User has an inverse hasMany relation to Post.
 */
final class InverseRelation extends AbstractRelation
{
    public function __construct(
        private readonly ForeignKey $foreignKey,
        private readonly string $relatedTableName,
    ) {}

    protected function computeName(): string
    {
        return ArHelper::getRelationName(
            $this->foreignKey->foreignColumnNames,
            $this->relatedTableName,
        );
    }

    public function getUnambiguousName(): string
    {
        // Combine the related table name with FK source column names to ensure a unique name.
        // E.g. for a FK `post.user_id -> user.id`, this gives `postUserId` instead of `post`.
        // Note: in the unlikely scenario where two different (tableName, columnName) pairs produce
        // the same concatenation (e.g. `user_profile` + `data` vs `user` + `profile_data`),
        // the resolved names would collide. This is an extremely rare edge case in practice.
        $combinedNames = array_map(
            fn(string $col) => $this->relatedTableName . '_' . $col,
            $this->foreignKey->columnNames,
        );

        return ArHelper::getRelationName($combinedNames, $this->relatedTableName, false);
    }

    public function getRelatedModel(): string
    {
        return (new Inflector())->tableToClass($this->relatedTableName);
    }

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
        return ArHelper::getRelationName(
            $this->foreignKey->columnNames,
            $this->foreignKey->foreignTableName,
        );
    }
}
