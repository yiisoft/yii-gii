<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Validator;

use InvalidArgumentException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Validator\Exception\UnexpectedRuleException;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\RuleHandlerInterface;
use Yiisoft\Validator\ValidationContext;

final class NewClassHandler implements RuleHandlerInterface
{
    public function __construct(
        private readonly Aliases $aliases,
    ) {
    }

    /**
     * An inline validator that checks if the attribute value refers to a valid namespaced class name.
     * The validator will check if the directory containing the new class file exist or not.
     *
     * @param mixed $value being validated
     */
    public function validate(mixed $value, object $rule, ValidationContext $context): Result
    {
        if (!$rule instanceof NewClassRule) {
            throw new UnexpectedRuleException(NewClassRule::class, $rule);
        }

        $result = new Result();
        $class = ltrim($value, '\\');
        if (($pos = strrpos($class, '\\')) !== false) {
            $ns = substr($class, 0, $pos);
            try {
                $path = $this->aliases->get('@' . str_replace('\\', '/', $ns));
                if (!is_dir($path)) {
                    $result->addError("Please make sure the directory containing this class exists: $path");
                }
            } catch (InvalidArgumentException) {
                $result->addError("The class namespace is invalid: $ns");
            }
        }

        return $result;
    }
}
