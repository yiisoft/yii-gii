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

        return match (true) {
            is_string($this->defaultValue) => "'" . addslashes($this->defaultValue) . "'",
            is_bool($this->defaultValue) => $this->defaultValue ? 'true' : 'false',
            null === $this->defaultValue => 'null',
            is_array($this->defaultValue) => var_export($this->defaultValue, true),
            default => (string)$this->defaultValue,
        };
    }

    /**
     * Returns true if getter should use null coalescing operator.
     */
    public function shouldUseNullCoalescing(): bool
    {
        return !$this->hasDefaultValue() && !$this->isAutoIncrement;
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
