<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use Yiisoft\Strings\Inflector;

use function implode;
use function in_array;
use function preg_match;
use function strlen;
use function strrpos;
use function strtolower;
use function substr;
use function trim;

final class ArHelper
{
    private const IDENTITY_FIELD_NAMES = ['id', 'uuid', 'key', 'code'];
    private const IDENTITY_ENDING_PATTERN = '/_(?:id|uuid|key|code)$/i';

    /**
     * Returns the relation name for the given column names.
     *
     * @param string[] $columnNames the column names.
     * @param string $defaultName the default relation name to use if a column name is an identity field name
     * (e.g. id, uuid, key, code). It can be a related table name or a related model name.
     * @param bool $isSingular whether the relation name should be singular.
     *
     * @return string
     */
    public static function getRelationName(array $columnNames, string $defaultName, bool $isSingular = true): string
    {
        $names = [];

        foreach ($columnNames as $columnName) {
            if (in_array(strtolower($columnName), self::IDENTITY_FIELD_NAMES, true)) {
                $names[] = $defaultName;
                continue;
            }

            if (preg_match(self::IDENTITY_ENDING_PATTERN, $columnName, $matches) === 1) {
                $names[] = substr($columnName, 0, -strlen($matches[0]));
                continue;
            }

            $names[] = $columnName;
        }

        $inflector = new Inflector();
        $name = implode(' ', $names);
        $name = $inflector->toWords($name);
        $name = strtolower($name);
        $name = self::changeLastWordForm($name, $isSingular);

        return $inflector->toCamelCase($name);
    }

    private static function changeLastWordForm(string $string, bool $isSingular): string
    {
        $lastSpacePos = strrpos($string, ' ');

        if ($lastSpacePos !== false) {
            $lastWord = substr($string, $lastSpacePos + 1);
            $string = substr($string, 0, $lastSpacePos);
        } else {
            $lastWord = $string;
            $string = '';
        }

        $inflector = new Inflector();

        $string .= $isSingular
            ? ' ' . $inflector->toSingular($lastWord)
            : ' ' . $inflector->toPlural($lastWord);

        return trim($string);
    }
}
