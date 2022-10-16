<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Validator;

use Attribute;
use Yiisoft\Validator\RuleInterface;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class TemplateRule implements RuleInterface
{
    public function getName(): string
    {
        return 'gii_template_rule';
    }

    public function getHandlerClassName(): string
    {
        return TemplateRuleHandler::class;
    }
}
