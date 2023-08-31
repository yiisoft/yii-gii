<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\CRUD;

use InvalidArgumentException;
use Yiisoft\ActiveRecord\ActiveRecordFactory;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Strings\StringHelper;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\Gii\Component\CodeFile\CodeFile;
use Yiisoft\Yii\Gii\Generator\AbstractGenerator;
use Yiisoft\Yii\Gii\GeneratorCommandInterface;
use Yiisoft\Yii\Gii\ParametersProvider;

/**
 * This generator will generate a controller and one or a few action view files.
 */
final class Generator extends AbstractGenerator
{
    public function __construct(
        Aliases $aliases,
        ValidatorInterface $validator,
        ParametersProvider $parametersProvider,
        private ActiveRecordFactory $activeRecordFactory,
    ) {
        parent::__construct($aliases, $validator, $parametersProvider);
    }

    public static function getId(): string
    {
        return 'crud';
    }

    public static function getName(): string
    {
        return 'CRUD';
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
        if (!$command instanceof Command) {
            throw new InvalidArgumentException();
        }

        $files = [];

        $rootPath = $this->aliases->get('@root');

        $codeFile = (new CodeFile(
            $this->getControllerFile($command),
            $this->render($command, 'controller.php')
        ))->withBasePath($rootPath);
        $files[$codeFile->getId()] = $codeFile;

        //$actions = $command->getActions();
        $actions = ['index'];
        $model = $this->activeRecordFactory->createAR($command->getModel());
        foreach ($actions as $action) {
            $codeFile = (new CodeFile(
                $this->getViewFile($command, $action),
                $this->render($command, $action . '.php', ['action' => $action, 'model' => $model])
            ))->withBasePath($rootPath);
            $files[$codeFile->getId()] = $codeFile;
        }

        return $files;
    }

    /**
     * @return string the controller class file path
     */
    private function getControllerFile(Command $command): string
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
    public function getViewFile(Command $command, string $action): string
    {
        $directory = empty($command->getViewsPath()) ? '@views/' : $command->getViewsPath();

        return $this->aliases->get(
            str_replace(
                ['\\', '//'],
                '/',
                sprintf(
                    '%s/%s/%s.php',
                    $directory,
                    StringHelper::lowercase(StringHelper::baseName($command->getModel())),
                    $action,
                ),
            ),
        );
    }

    public static function getCommandClass(): string
    {
        return Command::class;
    }
}
