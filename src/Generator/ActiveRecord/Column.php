<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use Yiisoft\Strings\Inflector;

final class Column
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly bool $isAllowNull,
        public readonly mixed $defaultValue,
        public readonly bool $isPrimaryKey = false,
        public readonly bool $isAutoIncrement = false,
        public readonly bool $hasDbDefaultExpression = false,
        public bool $isUsedInRelation = false,
    ) {
    }

    public function getPascalCaseName(): string
    {
        return (new Inflector())->toPascalCase($this->name);
    }

    /**
     * Returns true if the property should have a default value in the generated code.
     */
    public function hasDefaultValue(): bool
    {
        return $this->defaultValue !== null && !$this->hasDbDefaultExpression && !$this->isAutoIncrement;
    }

    /**
     * Returns the PHP representation of the default value for use in generated code.
     */
    public function getPhpDefaultValue(): string
    {
        if (!$this->hasDefaultValue()) {
            return '';
        }

        /** @psalm-suppress MixedArgument */
        return match (gettype($this->defaultValue)) {
            'string' => "'" . addslashes($this->defaultValue) . "'",
            'boolean' => $this->defaultValue ? 'true' : 'false',
            'NULL' => 'null',
            'array' => var_export($this->defaultValue, true),
            'integer', 'double' => (string)$this->defaultValue,
            default => var_export($this->defaultValue, true),
        };
    }

    /**
     * Returns the PHP code to initialize a DB expression in the constructor.
     */
    public function getDbExpressionInitializer(): string
    {
        if (!$this->hasDbDefaultExpression) {
            return '';
        }

        // Get the actual expression class
        $className = $this->defaultValue::class;

        // Get the SQL expression string by converting the object to string
        $expressionSql = (string)$this->defaultValue;

        // Return the code to create a new instance of the expression
        return 'new \\' . $className . '(' . var_export($expressionSql, true) . ')';
    }

    /**
     * Returns the fully qualified class name of the DB expression.
     */
    public function getDbExpressionClassName(): string
    {
        if (!$this->hasDbDefaultExpression) {
            return '';
        }

        return '\\' . $this->defaultValue::class;
    }

    /**
     * Returns true if getter should use null coalescing operator.
     */
    public function shouldUseNullCoalescing(): bool
    {
        return $this->canBeUninitialized();
    }

    /**
     * Returns true if the property can be uninitialized.
     * This happens when the property is auto-increment or has no default value.
     */
    public function canBeUninitialized(): bool
    {
        return $this->isAutoIncrement || !$this->hasDefaultValue();
    }

    /**
     * Returns true if setter should use ActiveRecord::set() method.
     * This is needed for primary keys and columns used in relationships.
     */
    public function shouldUseSetMethod(): bool
    {
        return $this->isPrimaryKey || $this->isUsedInRelation;
    }
}
