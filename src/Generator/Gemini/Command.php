<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\Gemini;

use Yiisoft\Validator\Rule\Required;
use Yiisoft\Yii\Gii\Generator\AbstractGeneratorCommand;
use Yiisoft\Yii\Gii\Validator\TemplateRule;

final class Command extends AbstractGeneratorCommand
{
    public function __construct(
        #[Required]
        private readonly string $prompt = 'Hello',
        #[Required]
        private readonly string $resultFilePath = 'src/gemini.txt',
        #[Required(message: 'A code template must be selected.')]
        #[TemplateRule]
        protected string $template = 'default',
    ) {
        parent::__construct($template);
    }

    public function getResultFilePath(): string
    {
        return $this->resultFilePath;
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public static function getAttributeLabels(): array
    {
        return [
            'resultFilePath' => 'Model namespace',
            'prompt' => 'Text prompt',
            'template' => 'Template',
        ];
    }

    public static function getHints(): array
    {
        return [
            'resultFilePath' => 'Namespace for the model class to store it in the related directory.',
            'prompt' => 'Text to ask.',
        ];
    }

    public static function getAttributes(): array
    {
        return [
            'prompt',
            'resultFilePath',
            'template',
        ];
    }
}
