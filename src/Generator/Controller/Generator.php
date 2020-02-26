<?php

namespace Yiisoft\Yii\Gii\Generator\Controller;

use Yiisoft\Strings\Inflector;
use Yiisoft\Strings\StringHelper;
use Yiisoft\Validator\Rule\MatchRegularExpression;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\Generator\AbstractGenerator;

/**
 * This generator will generate a controller and one or a few action view files.
 *
 * @property array $actionIDs An array of action IDs entered by the user. This property is read-only.
 * @property string $controllerFile The controller class file path. This property is read-only.
 * @property string $controllerID The controller ID. This property is read-only.
 * @property string $controllerNamespace The namespace of the controller class. This property is read-only.
 * @property string $controllerSubPath The controller sub path. This property is read-only.
 */
final class Generator extends AbstractGenerator
{
    /**
     * @var string the controller class name
     */
    public string $controllerClass;
    /**
     * @var string the controller's view path
     */
    public string $viewPath;
    /**
     * @var string the base class of the controller
     */
    public string $baseClass = 'App\\Controller';
    /**
     * @var string list of action IDs separated by commas or spaces
     */
    public string $actions = 'index';


    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Controller Generator';
    }

    /**
     * {@inheritdoc}
     */
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
                    (new MatchRegularExpression('/^[\w\\\\]*Controller$/'))
                        ->message('Only word characters and backslashes are allowed, and the class name must end with "Controller".')
                ],
                //['controllerClass', 'validateNewClass'],
                'baseClass' => [
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
            'viewPath' => 'View Path',
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
                provide a fully qualified namespaced class (e.g. <code>app\controllers\PostController</code>),
                and class name should be in CamelCase ending with the word <code>Controller</code>. Make sure the class
                is using the same namespace as specified by your application\'s controllerNamespace property.',
            'actions' => 'Provide one or multiple action IDs to generate empty action method(s) in the controller. Separate multiple action IDs with commas or spaces.
                Action IDs should be in lower case. For example:
                <ul>
                    <li><code>index</code> generates <code>actionIndex()</code></li>
                    <li><code>create-order</code> generates <code>actionCreateOrder()</code></li>
                </ul>',
            'viewPath' => 'Specify the directory for storing the view scripts for the controller. You may use path alias here, e.g.,
                <code>/var/www/yii-demo/controllers/views/order</code>, <code>@app/views/order</code>. If not set, it will default
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

        $files[] = new CodeFile(
            $this->getControllerFile(),
            $this->render('controller.php')
        );

        foreach ($this->getActionIDs() as $action) {
            $files[] = new CodeFile(
                $this->getViewFile($action),
                $this->render('view.php', ['action' => $action])
            );
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
        return $this->aliases->get('@src/Controller/' . $this->controllerClass) . '.php';
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
        if (empty($this->viewPath)) {
            return $this->aliases->get(
                '@views/' . $this->getControllerID() . "/$action.php"
            );
        }

        return $this->aliases->get(str_replace('\\', '/', $this->viewPath) . "/$action.php");
    }

    /**
     * @return string the namespace of the controller class
     */
    public function getControllerNamespace(): string
    {
        return $this->parameters->get('gii.controller.namespace');
    }
}
