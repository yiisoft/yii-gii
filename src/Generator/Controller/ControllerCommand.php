<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\Controller;

use Yiisoft\Strings\Inflector;
use Yiisoft\Strings\StringHelper;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Yii\Gii\Generator\AbstractGeneratorCommand;
use Yiisoft\Yii\Gii\Validator\NewClassRule;
use Yiisoft\Yii\Gii\Validator\TemplateRule;

final class ControllerCommand extends AbstractGeneratorCommand
{
    public function __construct(
        #[Required]
        #[Regex(
            pattern: "/^(?:[a-z][a-z0-9]*\\\\?)+(?<!\\\\)$/i",
            message: 'Invalid namespace'
        )]
        private string $controllerNamespace = 'App\\Controller',
        #[Required]
        #[Regex(
            pattern: '/^[A-Z][a-zA-Z0-9]*Controller$/',
            message: 'Only word characters are allowed, and the class name must start with a capital letter and end with "Controller".'
        )]
        #[NewClassRule]
        /**
         * @var string the controller class name
         */
        private string $controllerClass = 'IndexController',
        /**
         * @var string|null the controller's views path
         */
        private ?string $viewsPath = null,
        #[Regex(
            pattern: '/^[a-z\\\\]*$/i',
            message: 'Only word characters and backslashes are allowed.',
            skipOnEmpty: true,
        )]
        /**
         * @var string|null the base class of the controller or null if no parent class present
         */
        private ?string $baseClass = null,
        #[Each([
            new Regex(
                pattern: '/^[a-z][a-z0-9]*$/',
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
        protected string $template = 'default',
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

    public static function getAttributeLabels(): array
    {
        return [
            'baseClass' => 'Base Class',
            'controllerClass' => 'Controller Class',
            'viewsPath' => 'Views Path',
            'actions' => 'Action IDs',
        ];
    }

    public static function getHints(): array
    {
        return [
            'controllerClass' => 'This is the name of the controller class to be generated. You should
                provide a fully qualified namespaced class (e.g. <code>App\Controller\PostController</code>),
                and class name should be in CamelCase ending with the word <code>Controller</code>. Make sure the class
                is using the same namespace as specified by your application\'s controllerNamespace property.',
            'actions' => 'Provide one or multiple action IDs to generate empty action method(s) in the controller. Separate multiple action IDs with commas or spaces.
                Action IDs should be in lower case. For example:
                <ul>
                    <li><code>index</code> generates <code>index()</code></li>
                    <li><code>create-order</code> generates <code>createOrder()</code></li>
                </ul>',
            'viewsPath' => 'Specify the directory for storing the view scripts for the controller. You may use path alias here, e.g.,
                <code>/var/www/app/controllers/views/order</code>, <code>@app/views/order</code>. If not set, it will default
                to <code>@app/views/ControllerID</code>',
            'baseClass' => 'This is the class that the new controller class will extend from. Please make sure the class exists and can be autoloaded.',
        ];
    }

    public static function getAttributes(): array
    {
        return [
            'controllerNamespace',
            'controllerClass',
            'baseClass',
            'actions',
            'template',
        ];
    }
}
