<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Command;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Generator;
use Yiisoft\Yii\Gii\GeneratorCommandInterface;
use Yiisoft\Yii\Gii\GeneratorInterface;

/**
 * This is the command line version of Gii - a code generator.
 *
 * You can use this command to generate ActiveRecord models. For example,
 * to generate a model for a table, you can run:
 *
 * ```shell
 * $ ./yii gii:active-record user
 * ```
 */
#[AsCommand(name: 'gii:active-record')]
final class ActiveRecordCommand extends BaseGenerateCommand
{
    public function getGenerator(): GeneratorInterface
    {
        return $this->gii->getGenerator(Generator::getId());
    }

    protected function configure(): void
    {
        $this->setDescription('Gii ActiveRecord model generator')
            ->addArgument('table', InputArgument::REQUIRED, 'Name of the database table')
            ->addOption('namespace', 'ns', InputOption::VALUE_OPTIONAL, 'Model namespace', 'App\\Model')
            ->addOption('parent', 'p', InputOption::VALUE_OPTIONAL, 'Model parent class', ActiveRecord::class)
            ->addOption('visibility', 'v', InputOption::VALUE_OPTIONAL, 'Property visibility (private, protected, public)', 'protected')
            ->addOption('no-get-set', 'nogs', InputOption::VALUE_NONE, 'Do not generate getters and setters')
            ->addOption('no-relations', 'norel', InputOption::VALUE_NONE, 'Do not generate relations')
            ->addOption('repository', 'repo', InputOption::VALUE_NONE, 'Use RepositoryTrait');

        parent::configure();
    }

    protected function createGeneratorCommand(InputInterface $input): GeneratorCommandInterface
    {
        return new Command(
            table: (string) $input->getArgument('table'),
            namespace: (string) $input->getOption('namespace'),
            parentClass: (string) $input->getOption('parent'),
            propertyVisibility: (string) $input->getOption('visibility'),
            generateGettersSetters: !$input->getOption('no-get-set'),
            generateRelations: !$input->getOption('no-relations'),
            useRepositoryTrait: (bool) $input->getOption('repository'),
            template: (string) $input->getOption('template'),
        );
    }
}
