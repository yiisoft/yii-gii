<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Validator;

use Yiisoft\Validator\Exception\UnexpectedRuleException;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\RuleHandlerInterface;
use Yiisoft\Validator\ValidationContext;

final class ReservedKeywordHandler implements RuleHandlerInterface
{
    private const KEYWORDS = [
        '__class__',
        '__dir__',
        '__file__',
        '__function__',
        '__line__',
        '__method__',
        '__namespace__',
        '__trait__',
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'case',
        'catch',
        'callable',
        'cfunction',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'die',
        'do',
        'echo',
        'else',
        'elseif',
        'empty',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'eval',
        'exception',
        'exit',
        'extends',
        'final',
        'finally',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'include',
        'include_once',
        'instanceof',
        'insteadof',
        'interface',
        'isset',
        'list',
        'namespace',
        'new',
        'old_function',
        'or',
        'parent',
        'php_user_filter',
        'print',
        'private',
        'protected',
        'public',
        'require',
        'require_once',
        'return',
        'static',
        'switch',
        'this',
        'throw',
        'trait',
        'try',
        'unset',
        'use',
        'var',
        'while',
        'xor',
        'fn',
    ];

    public function validate(mixed $value, object $rule, ValidationContext $context): Result
    {
        if (!$rule instanceof ReservedKeywordRule) {
            throw new UnexpectedRuleException(ReservedKeywordRule::class, $rule);
        }

        $result = new Result();
        if (!is_string($value)) {
            $result->addError(sprintf('Value must be a string, %s given.".', gettype($value)));
            return $result;
        }

        if (self::isReservedKeyword($value)) {
            $result->addError(
                message: 'The value {value} is reserved keyword.',
                parameters: ['value' => $value],
            );
        }

        return $result;
    }

    /**
     * @param string $value the attribute to be validated
     *
     * @return bool whether the value is a reserved PHP keyword.
     */
    private static function isReservedKeyword(string $value): bool
    {
        return in_array(strtolower($value), self::KEYWORDS, true);
    }
}
