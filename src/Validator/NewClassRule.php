<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Validator;

use Attribute;
use Yiisoft\Validator\RuleInterface;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class NewClassRule implements RuleInterface
{
    public function getName(): string
    {
        return 'gii_new_class';
    }

    public function getHandlerClassName(): string
    {
        return NewClassHandler::class;
    }
}
