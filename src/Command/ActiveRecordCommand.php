<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Command;
use Yiisoft\Yii\Gii\Generator\ActiveRecord\Generator;
use Yiisoft\Yii\Gii\GeneratorCommandInterface;
use Yiisoft\Yii\Gii\GeneratorInterface;

use function filter_var;

use function in_array;
use function strtolower;

use const FILTER_VALIDATE_BOOL;

/**
 * This is the command line version of Gii - a code generator.
 *
 * You can use this command to generate ActiveRecord models. For example,
 * to generate a model for a table, you can run:
 *
 * ```
 * $ ./yii gii/active-record user
 * ```
 */
#[AsCommand(name: 'gii/active-record')]
final class ActiveRecordCommand extends BaseGenerateCommand
{
    protected function configure(): void
    {
        $this->setDescription('Gii ActiveRecord model generator')
            ->addArgument('tableName', InputArgument::REQUIRED, 'Name of the database table')
            ->addOption('namespace', 'ns', InputArgument::OPTIONAL, 'Model namespace')
            ->addOption('baseClass', 'b', InputArgument::OPTIONAL, 'Model base class')
            ->addOption('propertyVisibility', 'pv', InputArgument::OPTIONAL, 'Property visibility (private, protected, public)')
            ->addOption('generateGettersSetters', 'gs', InputArgument::OPTIONAL, 'Generate getters and setters (true/false)')
            ->addOption('generateRelations', 'gr', InputArgument::OPTIONAL, 'Generate relations (true/false)')
            ->addOption('useRepositoryTrait', 'rt', InputArgument::OPTIONAL, 'Use RepositoryTrait (true/false)');

        parent::configure();
    }

    public function getGenerator(): GeneratorInterface
    {
        return $this->gii->getGenerator(Generator::getId());
    }

    protected function createGeneratorCommand(InputInterface $input): GeneratorCommandInterface
    {
        /**
         * @var string|null $template
         */
        $template = $input->getOption('template');
        $template ??= 'default';

        return new Command(
            template: $template,
            namespace: (string) ($input->getOption('namespace') ?: 'App\\Model'),
            tableName: (string) $input->getArgument('tableName'),
            baseClass: (string) ($input->getOption('baseClass') ?: ActiveRecord::class),
            propertyVisibility: (string) ($input->getOption('propertyVisibility') ?: 'protected'),
            generateGettersSetters: $this->parseBoolOption($input, 'generateGettersSetters', true),
            generateRelations: $this->parseBoolOption($input, 'generateRelations', true),
            useRepositoryTrait: $this->parseBoolOption($input, 'useRepositoryTrait', false),
        );
    }

    private function parseBoolOption(InputInterface $input, string $name, bool $default): bool
    {
        $value = $input->getOption($name);
        if ($value === null) {
            return $default;
        }

        return in_array(strtolower((string) $value), ['1', 't', 'true', 'y', 'yes', 'on'], true);
    }
}
