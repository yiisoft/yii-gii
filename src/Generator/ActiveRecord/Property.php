<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\ActiveRecord;

use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Db\Schema\Column\ColumnInterface;
use Yiisoft\Strings\Inflector;
use Yiisoft\VarDumper\VarDumper;

use function array_diff;
use function array_unique;
use function count;
use function get_debug_type;
use function implode;
use function in_array;
use function is_object;
use function reset;
use function sprintf;
use function var_export;
use function is_array;
use function is_scalar;

final class Property
{
    public bool $usedInRelation = false;

    public function __construct(
        private readonly ColumnInterface $column,
    ) {}

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
        return $this->column->hasDefaultValue()
            && !$this->column->isAutoIncrement()
            && ($this->column->getDefaultValue() !== null || !$this->column->isNotNull());
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

    public function isDefaultValueExpression(): bool
    {
        return $this->column->getDefaultValue() instanceof Expression;
    }

    /**
     * Returns the PHP representation of the default value for use in generated code.
     */
    public function getDefaultValue(): string
    {
        $defaultValue = $this->column->getDefaultValue();

        if ($defaultValue instanceof Expression) {
            return sprintf(
                'new Expression(%s, %s)',
                var_export($defaultValue->expression, true),
                $defaultValue->params === []
                    ? '[]'
                    : VarDumper::create($defaultValue->params)->export(false),
            );
        }

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
        $types = [];

        if ($isNullable) {
            $types[] = 'null';
        }

        $defaultValue = $this->column->getDefaultValue();

        if ($defaultValue !== null) {
            $defaultValueType = get_debug_type($defaultValue);

            $types[] = (is_object($defaultValue) ? '\\' : '') . $defaultValueType;
        }

        $reflection = new ReflectionMethod($this->column, 'phpTypecast');
        $returnType = $reflection->getReturnType();

        if ($returnType instanceof ReflectionNamedType) {
            $typeName = $returnType->getName();

            if ($typeName === 'mixed') {
                return 'mixed';
            }

            $types[] = ($returnType->isBuiltin() ? '' : '\\') . $returnType->getName();
        } elseif ($returnType instanceof ReflectionUnionType) {
            /** @var ReflectionNamedType $type */
            foreach ($returnType->getTypes() as $type) {
                $types[] = ($type->isBuiltin() ? '' : '\\') . $type->getName();
            }
        } else {
            return 'mixed';
        }

        $types = array_unique($types);

        if (count($types) === 2 && in_array('null', $types, true)) {
            $types = array_diff($types, ['null']);

            /** @psalm-suppress PossiblyFalseOperand */
            return '?' . reset($types);
        }

        return implode('|', $types);
    }
}
