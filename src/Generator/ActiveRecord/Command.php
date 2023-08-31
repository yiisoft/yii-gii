<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Strings\Inflector;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Yii\Gii\Generator\AbstractGeneratorCommand;
use Yiisoft\Yii\Gii\Validator\TableExistsRule;
use Yiisoft\Yii\Gii\Validator\TemplateRule;

final class Command extends AbstractGeneratorCommand
{
    public function __construct(
        #[Required]
        #[Regex(
            pattern: '/^(?:[a-z][a-z0-9]*)(?:\\\\[a-z][a-z0-9]*)*$/i',
            message: 'Invalid namespace'
        )]
        private readonly string $namespace = 'App\\Model',
        #[Required]
        #[Regex(
            pattern: '/^(?:[a-z][a-z0-9]*)(?:\\\\[a-z][a-z0-9]*)*$/i',
            message: 'Invalid table name'
        )]
        #[TableExistsRule]
        private readonly string $tableName = 'user',
        #[Regex(
            pattern: '/^[a-z\\\\]*$/i',
            message: 'Only word characters and backslashes are allowed.',
            skipOnEmpty: true,
        )]
        private readonly string $baseClass = ActiveRecord::class,
        #[Required(message: 'A code template must be selected.')]
        #[TemplateRule]
        protected string $template = 'default',
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

    public static function getAttributeLabels(): array
    {
        return [
            'namespace' => 'Controller Namespace',
            'tableName' => 'Table name',
            'template' => 'Action IDs',
        ];
    }

    public static function getHints(): array
    {
        return [
            'controllerClass' => 'This is the name of the controller class to be generated. You should
                provide a fully qualified namespaced class (e.g. <code>App\Controller\PostController</code>),
                and class name should be in CamelCase ending with the word <code>Controller</code>. Make sure the class
                is using the same namespace as specified by your application\'s namespace property.',
            'baseClass' => 'This is the class that the new controller class will extend from. Please make sure the class exists and can be autoloaded.',
        ];
    }

    public static function getAttributes(): array
    {
        return [
            'namespace',
            'tableName',
            'template',
        ];
    }
}
