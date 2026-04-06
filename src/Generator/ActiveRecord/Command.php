<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Strings\Inflector;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Yii\Gii\Generator\AbstractGeneratorCommand;
use Yiisoft\Yii\Gii\Validator\ClassExistsRule;
use Yiisoft\Yii\Gii\Validator\TableExistsRule;
use Yiisoft\Yii\Gii\Validator\TemplateRule;

final class Command extends AbstractGeneratorCommand
{
    public function __construct(

        string $template = 'default',
        #[Required]
        #[Regex(
            pattern: '/^[a-z][a-z0-9]*(?:\\[a-z][a-z0-9]*)*$/i',
            message: 'Invalid namespace'
        )]
        private readonly string $namespace = 'App\\Model',
        #[Required]
        #[Regex(
            pattern: '/^[\w\-.]+$/i',
            message: 'Invalid table name'
        )]
        #[TableExistsRule]
        private readonly string $tableName = 'user',
        #[Regex(
            pattern: '/^[a-z\\\\]*$/i',
            message: 'Only word characters and backslashes are allowed.',
            skipOnEmpty: true,
        )]
        #[ClassExistsRule]
        private readonly string $baseClass = ActiveRecord::class,
        private readonly string $propertyVisibility = 'protected',
        private readonly bool $generateGettersSetters = true,
        private readonly bool $generateRelations = true,
        private readonly bool $useRepositoryTrait = false,
        private readonly bool $usePrivatePropertiesTrait = false,
    ) {
        parent::__construct($template);
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getBaseClass(): string
    {
        return $this->baseClass;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getModelName(): string
    {
        return (new Inflector())->tableToClass($this->tableName);
    }

    public function getPropertyVisibility(): string
    {
        return $this->propertyVisibility;
    }

    public function isGenerateGettersSetters(): bool
    {
        return $this->generateGettersSetters;
    }

    public function isGenerateRelations(): bool
    {
        return $this->generateRelations;
    }

    public function isUseRepositoryTrait(): bool
    {
        return $this->useRepositoryTrait;
    }

    public function isUsePrivatePropertiesTrait(): bool
    {
        return $this->usePrivatePropertiesTrait;
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
            'usePrivatePropertiesTrait' => 'Use PrivatePropertiesTrait',
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
            'usePrivatePropertiesTrait' => 'Whether to include PrivatePropertiesTrait (required for private properties).',
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
            'usePrivatePropertiesTrait',
        ];
    }
}
