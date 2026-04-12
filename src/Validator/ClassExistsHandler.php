<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Validator;

use Yiisoft\Validator\Exception\UnexpectedRuleException;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\RuleHandlerInterface;
use Yiisoft\Validator\ValidationContext;

use function is_subclass_of;

/**
 * An inline validator that checks if the attribute value refers to an existing class name.
 */
final class ClassExistsHandler implements RuleHandlerInterface
{
    public function validate(mixed $value, object $rule, ValidationContext $context): Result
    {
        if (!$rule instanceof ClassExistsRule) {
            throw new UnexpectedRuleException(ClassExistsRule::class, $rule);
        }

        $result = new Result();
        if (!is_string($value)) {
            $result->addError(sprintf('Value must be a string, %s given.".', gettype($value)));
            return $result;
        }

        if (!class_exists($value)) {
            $result->addError("Class '$value' does not exist or has syntax error.");
        }

        if ($rule->parent !== null && !is_subclass_of($value, $rule->parent)) {
            $result->addError("Class '$value' is not a subclass of '$rule->parent'.");
        }

        return $result;
    }
}
