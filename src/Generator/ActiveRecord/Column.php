<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

final class Column
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $isAllowNull,
        public mixed $defaultValue,
        public bool $isPrimaryKey = false,
        public bool $isAutoIncrement = false,
        public bool $hasDbDefaultExpression = false,
        public bool $isUsedInRelation = false,
    ) {
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
        $className = get_class($this->defaultValue);

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

        return '\\' . get_class($this->defaultValue);
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
