<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use Yiisoft\Strings\Inflector;

use function implode;
use function in_array;
use function preg_match;
use function strlen;
use function strtolower;
use function substr;

final class ArHelper
{
    private const IDENTITY_FIELD_NAMES = ['id', 'uuid', 'key', 'code'];
    private const IDENTITY_ENDING_PATTERN = '/_(?:id|uuid|key|code)$/';

    /**
     * Returns the relation name for the given column names.
     *
     * @param string[] $columnNames the column names of the related table.
     * @param string $defaultName the default relation name to use if a column name is an identity field name
     * (e.g. id, uuid, key, code). It can be a related table name or a related model name.
     *
     * @return string
     */
    public static function getRelationName(array $columnNames, string $defaultName): string
    {
        $names = [];

        foreach ($columnNames as $columnName) {
            $name = strtolower($columnName);

            if (in_array($name, self::IDENTITY_FIELD_NAMES, true)) {
                $names[] = $defaultName;
                continue;
            }

            if (preg_match(self::IDENTITY_ENDING_PATTERN, $name, $matches) === 1) {
                $names[] = substr($name, 0, -strlen($matches[0]));
                continue;
            }

            $names[] = $name;
        }

        $name = implode('_', $names);

        return (new Inflector())->toCamelCase($name);
    }
}
