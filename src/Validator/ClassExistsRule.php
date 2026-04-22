<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Validator;

use Attribute;
use Yiisoft\Validator\RuleHandlerInterface;
use Yiisoft\Validator\RuleInterface;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class ClassExistsRule implements RuleInterface
{
    /**
     * @param class-string|null $parent Parent class name or interface to check against.
     */
    public function __construct(
        public readonly ?string $parent = null,
    ) {
    }

    public function getHandler(): string|RuleHandlerInterface
    {
        return ClassExistsHandler::class;
    }
}
