<?php

namespace Yiisoft\Yii\Gii\Command;

use Symfony\Component\Console\Input\InputArgument;

/**
 * This is the command line version of Gii - a code generator.
 *
 * You can use this command to generate models, controllers, etc. For example,
 * to generate an ActiveRecord model based on a DB table, you can run:
 *
 * ```
 * $ ./yii gii/model --tableName=city --modelClass=City
 * ```
 */
class ControllerCommand extends BaseGenerateCommand
{
    protected const NAME = 'controller';
    protected static $defaultName = 'gii/controller';

    protected function configure(): void
    {
        $this->setDescription('Gii controller generator')
            ->addArgument('className', InputArgument::REQUIRED, 'Name of the generated controller');
        parent::configure();
    }
}
