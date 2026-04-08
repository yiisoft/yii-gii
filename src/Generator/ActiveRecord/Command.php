<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Strings\Inflector;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Yii\Gii\Generator\AbstractGeneratorCommand;
use Yiisoft\Yii\Gii\Validator\ClassExistsRule;
use Yiisoft\Yii\Gii\Validator\TableExistsRule;

final class Command extends AbstractGeneratorCommand
{
    public function __construct(
        #[Required]
        #[Regex(
            pattern: '/^[\w\-.]+$/',
            message: 'Invalid table name'
        )]
        #[TableExistsRule]
        public readonly string $tableName,
        string $template = 'default',
        #[Required]
        #[Regex(
            pattern: '/^[a-z][a-z0-9]*(?:\\\\[a-z][a-z0-9]*)*$/i',
            message: 'Invalid namespace'
        )]
        public readonly string $namespace = 'App\\Model',
        #[Required]
        #[Regex(
            pattern: '/^[a-z\\\\]*$/i',
            message: 'Only word characters and backslashes are allowed.',
            skipOnEmpty: true,
        )]
        #[ClassExistsRule]
        public readonly string $baseClass = ActiveRecord::class,
        #[Required]
        #[In(['private', 'protected', 'public'])]
        public readonly string $propertyVisibility = 'protected',
        public readonly bool $generateGettersSetters = true,
        public readonly bool $generateRelations = true,
        public readonly bool $useRepositoryTrait = false,
    ) {
        parent::__construct($template);
    }

    public function getModelName(): string
    {
        return (new Inflector())->tableToClass($this->tableName);
    }

    public function usePrivatePropertiesTrait(): bool
    {
        return $this->propertyVisibility === 'private';
    }

    public static function getAttributeLabels(): array
    {
        return [
            'namespace' => 'Model namespace',
            'baseClass' => 'Base class',
            'tableName' => 'Table name',
            'template' => 'Template',
            'propertyVisibility' => 'Property visibility',
            'generateGettersSetters' => 'Generate getters and setters',
            'generateRelations' => 'Generate relations',
            'useRepositoryTrait' => 'Use RepositoryTrait',
        ];
    }

    public static function getHints(): array
    {
        return [
            'namespace' => 'Namespace for the model class to store it in the related directory.',
            'baseClass' => 'Parent class for the new model class.',
            'tableName' => 'Corresponded table name for the model class.',
            'propertyVisibility' => 'Visibility for properties: private, protected, or public.',
            'generateGettersSetters' => 'Whether to generate getter and setter methods for properties.',
            'generateRelations' => 'Whether to generate relation methods based on foreign keys.',
            'useRepositoryTrait' => 'Whether to include RepositoryTrait in the generated model.',
        ];
    }

    public static function getAttributes(): array
    {
        return [
            'namespace',
            'tableName',
            'baseClass',
            'template',
            'propertyVisibility',
            'generateGettersSetters',
            'generateRelations',
            'useRepositoryTrait',
        ];
    }
}
