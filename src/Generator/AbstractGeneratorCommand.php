<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator;

use Yiisoft\Validator\Rule\Required;
use Yiisoft\Yii\Gii\Validator\TemplateRule;

class AbstractGeneratorCommand
{
    public function __construct(
        #[Required(message: 'A code template must be selected.')]
        #[TemplateRule]
        private string $template = 'default',
    ) {
    }

    public function getTemplate(): string
    {
        return $this->template;
    }
}
