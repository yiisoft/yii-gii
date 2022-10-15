<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\Controller;

use Yiisoft\Strings\Inflector;
use Yiisoft\Strings\StringHelper;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Yii\Gii\Generator\AbstractGeneratorCommand;
use Yiisoft\Yii\Gii\Validator\NewClassRule;
use Yiisoft\Yii\Gii\Validator\TemplateRule;

class ControllerCommand extends AbstractGeneratorCommand
{
    public function __construct(
        private string $controllerNamespace = 'App\\Controller',

        #[Required]
        #[Regex(
            pattern: '/^[A-Z][\w]*Controller$/',
            message: 'Only word characters are allowed, and the class name must start with a capital letter and end with "Controller".'
        )]
        #[NewClassRule]
        /**
         * @var string the controller class name
         */
        private string $controllerClass = '',
        /**
         * @var string|null the controller's views path
         */
        private ?string $viewsPath = null,

        #[Regex(
            pattern: '/^[\w\\\\]*$/',
            message: 'Only word characters and backslashes are allowed.',
            skipOnEmpty: true,
        )]
        /**
         * @var string|null the base class of the controller or null if no parent class present
         */
        private ?string $baseClass = null,

        #[Each([
            new Regex(
                pattern: '/^[a-z][a-z0-9\\-,\\s]*$/',
                message: 'Only a-z, 0-9, dashes (-), spaces and commas are allowed.'
            ),
        ])
        ]
        /**
         * @var string[] list of action IDs
         */
        private array $actions = ['index'],

        #[Required(message: 'A code template must be selected.')]
        #[TemplateRule]
        private string $template = 'default',
    ) {
        parent::__construct($template);
        sort($this->actions);

    }

    /**
     * @return string the controller ID
     */
    public function getControllerID(): string
    {
        $name = StringHelper::baseName($this->controllerClass);
        return (new Inflector())->pascalCaseToId(substr($name, 0, -10));
    }

    public function getControllerClass(): string
    {
        return $this->controllerClass;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function getViewsPath(): ?string
    {
        return $this->viewsPath;
    }

    public function getControllerNamespace(): string
    {
        return $this->controllerNamespace;
    }

    public function getBaseClass(): ?string
    {
        return $this->baseClass;
    }
}
