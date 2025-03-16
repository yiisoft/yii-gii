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
    ) {
    }
}
