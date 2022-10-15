<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Yiisoft\Yii\Gii\GeneratorInterface;

/**
 * This is the command line version of Gii - a code generator.
 *
 * You can use this command to generate controllers, actions, etc. For example,
 * to generate a controller with some actions, you can run:
 *
 * ```
 * $ ./yii gii/controller OrderController --actions=index,view,edit
 * ```
 */
final class ControllerCommand extends BaseGenerateCommand
{
    private const NAME = 'controller';
    protected static $defaultName = 'gii/controller';

    protected function configure(): void
    {
        $this->setDescription('Gii controller generator')
            ->addArgument('controllerClass', InputArgument::REQUIRED, 'Name of the generated controller')
            ->addOption('viewsPath', 'vp', InputArgument::OPTIONAL, 'Controller views path')
            ->addOption('baseClass', 'b', InputArgument::OPTIONAL, 'Controller base class')
            ->addOption('actions', 'a', InputArgument::OPTIONAL, 'Name of the controller actions');
        parent::configure();
    }

    public function getGenerator(): GeneratorInterface
    {
        return $this->gii->getGenerator(self::NAME);
    }

    public function createGeneratorCommand(InputInterface $input): \Yiisoft\Yii\Gii\Generator\Controller\ControllerCommand
    {
        $actions = $input->getOption('actions');
        $actions = $actions !== null ? explode(',', $actions) : ['index'];

        $template = $input->getOption('template');
        $template = $template !== null ? $template : 'default';

        return new \Yiisoft\Yii\Gii\Generator\Controller\ControllerCommand(
            controllerClass: $input->getArgument('controllerClass'),
            viewsPath: $input->getOption('viewsPath'),
            baseClass: $input->getOption('baseClass'),
            actions: $actions,
            template: $template,
        );
    }
}
