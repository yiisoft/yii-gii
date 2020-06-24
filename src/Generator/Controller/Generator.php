<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\Controller;

use Yiisoft\Strings\Inflector;
use Yiisoft\Strings\StringHelper;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\MatchRegularExpression;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\Generator\AbstractGenerator;

/**
 * This generator will generate a controller and one or a few action view files.
 */
final class Generator extends AbstractGenerator
{
    private string $controllerNamespace = 'App\\Controller';
    /**
     * @var string the controller class name
     */
    private string $controllerClass = '';
    /**
     * @var null|string the controller's views path
     */
    private ?string $viewsPath = null;
    /**
     * @var string the base class of the controller
     */
    private string $baseClass = 'App\\Controller';
    /**
     * @var string list of action IDs separated by commas or spaces
     */
    private string $actions = 'index';

    public function getName(): string
    {
        return 'Controller Generator';
    }

    public function getDescription(): string
    {
        return 'This generator helps you to quickly generate a new controller class with
            one or several controller actions and their corresponding views.';
    }

    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'controllerClass' => [
                    new Required(),
                    (new MatchRegularExpression('/^[A-Z][\w]*Controller$/'))
                        ->message(
                            'Only word characters are allowed, and the class name must start with a capital letter and end with "Controller".'
                        ),
                    (new Callback([$this, 'validateNewClass']))
                ],
                'baseClass' => [
                    new Required(),
                    (new MatchRegularExpression('/^[\w\\\\]*$/'))
                        ->message('Only word characters and backslashes are allowed.')
                ],
                'actions' => [
                    (new MatchRegularExpression('/^[a-z][a-z0-9\\-,\\s]*$/'))
                        ->message('Only a-z, 0-9, dashes (-), spaces and commas are allowed.')
                ]
            ]
        );
    }

    public function attributeLabels(): array
    {
        return [
            'baseClass' => 'Base Class',
            'controllerClass' => 'Controller Class',
            'viewsPath' => 'Views Path',
            'actions' => 'Action IDs',
        ];
    }

    public function requiredTemplates(): array
    {
        return [
            'controller.php',
            'view.php',
        ];
    }

    public function stickyAttributes(): array
    {
        return ['baseClass'];
    }

    public function hints(): array
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

    public function successMessage(): string
    {
        return 'The controller has been generated successfully.';
    }

    public function generate(): array
    {
        $files = [];

        $files[] = (new CodeFile(
            $this->getControllerFile(),
            $this->render('controller')
        ))->withBasePath($this->aliases->get('@root'));

        foreach ($this->getActionIDs() as $action) {
            $files[] = (new CodeFile(
                $this->getViewFile($action),
                $this->render('view', ['action' => $action])
            ))->withBasePath($this->aliases->get('@root'));
        }

        return $files;
    }

    /**
     * Normalizes [[actions]] into an array of action IDs.
     * @return array an array of action IDs entered by the user
     */
    public function getActionIDs(): array
    {
        $actions = array_unique(preg_split('/[\s,]+/', $this->actions, -1, PREG_SPLIT_NO_EMPTY));
        sort($actions);

        return $actions;
    }

    /**
     * @return string the controller class file path
     */
    public function getControllerFile(): string
    {
        return $this->aliases->get(
            sprintf('%s/%s.php', $this->getDirectory(), $this->getControllerClass())
        );
    }

    /**
     * @return string the controller ID
     */
    public function getControllerID(): string
    {
        $name = StringHelper::basename($this->controllerClass);
        return (new Inflector())->camel2id(substr($name, 0, strlen($name) - 10));
    }

    /**
     * @param string $action the action ID
     * @return string the action view file path
     */
    public function getViewFile(string $action): string
    {
        if (empty($this->getViewsPath())) {
            return $this->aliases->get(
                '@views/' . $this->getControllerID() . "/$action.php"
            );
        }

        return $this->aliases->get(str_replace('\\', '/', $this->getViewsPath()) . "/$action.php");
    }

    /**
     * @return string the namespace of the controller class
     */
    public function getControllerNamespace(): string
    {
        return $this->controllerNamespace;
    }

    /**
     * @param string $controllerNamespace
     */
    public function setControllerNamespace(string $controllerNamespace): void
    {
        $this->controllerNamespace = $controllerNamespace;
    }

    public function getControllerClass(): string
    {
        return $this->controllerClass;
    }

    public function setControllerClass(string $controllerClass): void
    {
        $this->controllerClass = $controllerClass;
    }

    public function getViewsPath(): ?string
    {
        return $this->viewsPath;
    }

    public function setViewsPath(?string $viewsPath): void
    {
        $this->viewsPath = $viewsPath;
    }

    public function getBaseClass(): string
    {
        return $this->baseClass;
    }

    public function setBaseClass(string $baseClass): void
    {
        $this->baseClass = $baseClass;
    }

    public function getActions(): string
    {
        return $this->actions;
    }

    public function setActions(string $actions): void
    {
        $this->actions = $actions;
    }
}
