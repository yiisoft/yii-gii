<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use Yiisoft\Db\Expression\ExpressionInterface;
use Yiisoft\Db\Schema\Column\ColumnInterface;
use Yiisoft\Strings\Inflector;

final class Property
{
    public bool $usedInRelation = false;

    public function __construct(
        private readonly ColumnInterface $column,
    ) {
    }

    public function getName(): string
    {
        return (string) $this->column->getName();
    }

    public function getPascalCaseName(): string
    {
        return (new Inflector())->toPascalCase($this->getName());
    }

    public function getType(): string
    {
        $isNullable = !$this->column->isNotNull();

        return $this->getPhpType($isNullable);
    }

    public function getReturnType(): string
    {
        $isNullable = !$this->column->isNotNull() || $this->isUninitialized();

        return $this->getPhpType($isNullable);
    }

    /**
     * Returns true if the property has a default value as a constant.
     */
    public function isDefaultValueConstant(): bool
    {
        if (!$this->column->hasDefaultValue() || $this->column->isAutoIncrement()) {
            return false;
        }

        $defaultValue = $this->column->getDefaultValue();

        if ($defaultValue === null) {
            return !$this->column->isNotNull();
        }

        return is_scalar($defaultValue) || is_array($defaultValue);
    }

    public function isDefaultValueExpression(): bool
    {
        return $this->column->getDefaultValue() instanceof ExpressionInterface;
    }

    /**
     * Returns the PHP representation of the default value for use in generated code.
     */
    public function getDefaultValueConstant(): string
    {
        if (!$this->isDefaultValueConstant()) {
            return '';
        }

        $defaultValue = $this->column->getDefaultValue();

        /** @psalm-suppress MixedArgument */
        return match (gettype($defaultValue)) {
            'string' => "'" . addslashes($defaultValue) . "'",
            'boolean' => $defaultValue ? 'true' : 'false',
            'NULL' => 'null',
            'array' => var_export($defaultValue, true),
            'integer', 'double' => (string) $defaultValue,
            default => var_export($defaultValue, true),
        };
    }

    /**
     * Returns the PHP code to initialize a DB expression in the constructor.
     */
    public function getDbExpressionInitializer(): string
    {
        if (!$this->isDefaultValueExpression()) {
            return '';
        }

        $defaultValue = $this->column->getDefaultValue();

        // Get the actual expression class
        $className = $defaultValue::class;

        // Get the SQL expression string by converting the object to string
        $expressionSql = (string) $defaultValue;

        // Return the code to create a new instance of the expression
        return 'new \\' . $className . '(' . var_export($expressionSql, true) . ')';
    }

    /**
     * Returns true if the property can be uninitialized.
     * This happens when the property is auto-increment or has no default value.
     */
    public function isUninitialized(): bool
    {
        return $this->column->isAutoIncrement() || !$this->isDefaultValueConstant();
    }

    /**
     * Returns true if setter should use ActiveRecord::set() method.
     * This is needed for primary keys and columns used in relationships.
     */
    public function shouldUseSetMethod(): bool
    {
        return $this->column->isPrimaryKey() || $this->usedInRelation;
    }

    private function getPhpType(bool $isNullable): string
    {
        $reflection = new ReflectionMethod($this->column, 'phpTypecast');
        $returnType = $reflection->getReturnType();

        if ($returnType instanceof ReflectionNamedType) {
            return ($isNullable ? '?' : '') . ($returnType->isBuiltin() ? '' : '\\') . $returnType->getName();
        }

        if ($returnType instanceof ReflectionUnionType) {
            $types = [];
            foreach ($returnType->getTypes() as $type) {
                $types[] = ($type->isBuiltin() ? '' : '\\') . $type->getName();
            }

            return implode('|', $types);
        }

        return ($isNullable ? '?' : '') . 'mixed';
    }
}
