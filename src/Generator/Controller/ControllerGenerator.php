<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\Controller;

use InvalidArgumentException;
use Yiisoft\Yii\Gii\Component\CodeFile\CodeFile;
use Yiisoft\Yii\Gii\Generator\AbstractGenerator;
use Yiisoft\Yii\Gii\Generator\AbstractGeneratorCommand;
use Yiisoft\Yii\Gii\GeneratorCommandInterface;

/**
 * This generator will generate a controller and one or a few action view files.
 */
final class ControllerGenerator extends AbstractGenerator
{
    public static function getId(): string
    {
        return 'controller';
    }

    public static function getName(): string
    {
        return 'Controller';
    }

    public static function getDescription(): string
    {
        return 'This generator helps you to quickly generate a new controller class with
            one or several controller actions and their corresponding views.';
    }

    public function getRequiredTemplates(): array
    {
        return [
            'controller.php',
            'view.php',
        ];
    }

    public function doGenerate(GeneratorCommandInterface $command): array
    {
        if (!$command instanceof ControllerCommand) {
            throw new InvalidArgumentException();
        }

        $files = [];

        $rootPath = $this->aliases->get('@root');

        $codeFile = (new CodeFile(
            $this->getControllerFile($command),
            $this->render($command, 'controller.php')
        ))->withBasePath($rootPath);
        $files[$codeFile->getId()] = $codeFile;

        foreach ($command->getActions() as $action) {
            $codeFile = (new CodeFile(
                $this->getViewFile($command, $action),
                $this->render($command, 'view.php', ['action' => $action])
            ))->withBasePath($rootPath);
            $files[$codeFile->getId()] = $codeFile;
        }

        return $files;
    }

    /**
     * @return string the controller class file path
     */
    private function getControllerFile(ControllerCommand $command): string
    {
        $directory = empty($command->getDirectory()) ? '@src/Controller/' : $command->getDirectory();

        return $this->aliases->get(
            str_replace(
                ['\\', '//'],
                '/',
                sprintf(
                    '%s/%s.php',
                    $directory,
                    $command->getControllerClass(),
                ),
            ),
        );
    }

    /**
     * @param string $action the action ID
     *
     * @return string the action view file path
     */
    public function getViewFile(ControllerCommand $command, string $action): string
    {
        $directory = empty($command->getViewsPath()) ? '@views/' : $command->getViewsPath();

        return $this->aliases->get(
            str_replace(
                ['\\', '//'],
                '/',
                sprintf(
                    '%s/%s/%s.php',
                    $directory,
                    $command->getControllerID(),
                    $action,
                ),
            ),
        );
    }

    public static function getCommandClass(): string
    {
        return ControllerCommand::class;
    }
}
