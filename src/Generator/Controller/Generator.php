<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\Controller;

use Closure;
use InvalidArgumentException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Strings\Inflector;
use Yiisoft\Strings\StringHelper;
use Yiisoft\Validator\Exception\UnexpectedRuleException;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RuleInterface;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\Generator\AbstractGenerator;
use Yiisoft\Yii\Gii\Generator\AbstractGeneratorCommand;
use Yiisoft\Yii\Gii\GiiParametersProvider;
use Yiisoft\Yii\Gii\Validator\NewClassRule;

/**
 * This generator will generate a controller and one or a few action view files.
 */
final class Generator extends AbstractGenerator
{
    public function getName(): string
    {
        return 'Controller Generator';
    }

    public function getDescription(): string
    {
        return 'This generator helps you to quickly generate a new controller class with
            one or several controller actions and their corresponding views.';
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

    public function generate(AbstractGeneratorCommand $command): array
    {
        if (!$command instanceof ControllerCommand) {
            throw new InvalidArgumentException();
        }

        $files = [];

        $files[] = (new CodeFile(
            $this->getControllerFile($command),
            $this->render($command, 'controller')
        ))->withBasePath($this->aliases->get('@root'));

        foreach ($command->getActions() as $action) {
            $files[] = (new CodeFile(
                $this->getViewFile($command, $action),
                $this->render($command, 'view', ['action' => $action])
            ))->withBasePath($this->aliases->get('@root'));
        }

        return $files;
    }

    /**
     * Normalizes [[actions]] into an array of action IDs.
     *
     * @return array an array of action IDs entered by the user
     */
    public function getActionIDs(ControllerCommand $command): array
    {
        return $command->getActions();
    }

    /**
     * @return string the controller class file path
     */
    private function getControllerFile(ControllerCommand $command): string
    {
        return $this->aliases->get(
            sprintf('%s/%s.php', $this->getDirectory() ?? '', $command->getControllerClass())
        );
    }

    /**
     * @param string $action the action ID
     *
     * @return string the action view file path
     */
    public function getViewFile(ControllerCommand $command, string $action): string
    {
        if (empty($command->getViewsPath())) {
            return $this->aliases->get(
                '@views/' . $command->getControllerID() . "/$action.php"
            );
        }

        return $this->aliases->get(str_replace('\\', '/', $command->getViewsPath()) . "/$action.php");
    }
}
