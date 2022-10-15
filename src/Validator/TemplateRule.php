<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Validator;

use Attribute;
use Yiisoft\Validator\SerializableRuleInterface;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class TemplateRule implements SerializableRuleInterface
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'gii_template_rule';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getHandlerClassName(): string
    {
        return TemplateRuleHandler::class;
    }
}
