<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use Yiisoft\Db\Schema\Column\ColumnInterface;
use Yiisoft\Strings\Inflector;
use Yiisoft\VarDumper\VarDumper;

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

    public function hasDefaultValue(): bool
    {
        return $this->column->hasDefaultValue() && !$this->column->isAutoIncrement();
    }

    /**
     * Returns true if the property has a default value as a constant.
     */
    public function isDefaultValueConstant(): bool
    {
        return $this->hasDefaultValue() && $this->isDefaultValueConstantInternal();
    }

    public function isDefaultValueNotConstant(): bool
    {
        return $this->hasDefaultValue() && !$this->isDefaultValueConstantInternal();
    }

    /**
     * Returns the PHP representation of the default value for use in generated code.
     */
    public function getDefaultValue(): string
    {
        $defaultValue = $this->column->getDefaultValue();

        return VarDumper::create($defaultValue)->export(false);
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

    private function isDefaultValueConstantInternal(): bool
    {
        $defaultValue = $this->column->getDefaultValue();

        if ($defaultValue === null) {
            return !$this->column->isNotNull();
        }

        return is_scalar($defaultValue) || is_array($defaultValue);
    }

    private function getPhpType(bool $isNullable): string
    {
        $reflection = new ReflectionMethod($this->column, 'phpTypecast');
        $returnType = $reflection->getReturnType();

        if ($returnType instanceof ReflectionNamedType) {
            $typeName = $returnType->getName();

            if ($typeName === 'mixed') {
                return 'mixed';
            }

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
