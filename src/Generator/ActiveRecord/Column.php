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
            is_null($this->defaultValue) => 'null',
            is_array($this->defaultValue) => '[]',
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
}
