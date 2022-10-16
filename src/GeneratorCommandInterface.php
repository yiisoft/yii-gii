<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

interface GeneratorCommandInterface
{
    public static function getAttributeLabels(): array;

    public static function hints(): array;
}
