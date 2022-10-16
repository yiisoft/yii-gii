<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\Controller;

use InvalidArgumentException;
use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\Generator\AbstractGenerator;
use Yiisoft\Yii\Gii\Generator\AbstractGeneratorCommand;

/**
 * This generator will generate a controller and one or a few action view files.
 */
final class ControllerGenerator extends AbstractGenerator
{
    public static function getName(): string
    {
        return 'Controller Generator';
    }

    public static function getDescription(): string
    {
        return 'This generator helps you to quickly generate a new controller class with
            one or several controller actions and their corresponding views.';
    }

    public static function getId(): string
    {
        return 'controller';
    }

    public function requiredTemplates(): array
    {
        return [
            'controller.php',
            'view.php',
        ];
    }

    public function doGenerate(AbstractGeneratorCommand $command): array
    {
        if (!$command instanceof ControllerCommand) {
            throw new InvalidArgumentException();
        }

        $files = [];

        $rootPath = $this->aliases->get('@root');

        $files[] = (new CodeFile(
            $this->getControllerFile($command),
            $this->render($command, 'controller')
        ))->withBasePath($rootPath);

        foreach ($command->getActions() as $action) {
            $files[] = (new CodeFile(
                $this->getViewFile($command, $action),
                $this->render($command, 'view', ['action' => $action])
            ))->withBasePath($rootPath);
        }

        return $files;
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
                "@views/{$command->getControllerID()}/$action.php"
            );
        }

        return $this->aliases->get(str_replace('\\', '/', $command->getViewsPath()) . "/$action.php");
    }

    public static function getCommandClass(): string
    {
        return ControllerCommand::class;
    }
}
