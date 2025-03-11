<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Validator;

use Attribute;
use Yiisoft\Validator\RuleHandlerInterface;
use Yiisoft\Validator\RuleInterface;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class ClassExistsRule implements RuleInterface
{
    #[\Override]
    public function getHandler(): string|RuleHandlerInterface
    {
        return ClassExistsHandler::class;
    }
}
