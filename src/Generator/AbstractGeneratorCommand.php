<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator;

use Yiisoft\Validator\Rule\Required;
use Yiisoft\Yii\Gii\GeneratorCommandInterface;
use Yiisoft\Yii\Gii\Validator\TemplateRule;

abstract class AbstractGeneratorCommand implements GeneratorCommandInterface
{
    public function __construct(
        #[Required(message: 'A code template must be selected.')]
        #[TemplateRule]
        protected string $template = 'default',
    ) {
    }

    #[\Override]
    public function getTemplate(): string
    {
        return $this->template;
    }
}
