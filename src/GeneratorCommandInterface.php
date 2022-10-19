<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

interface GeneratorCommandInterface
{
    public static function getAttributes(): array;

    public static function getAttributeLabels(): array;

    public static function getHints(): array;

    public function getTemplate(): string;
}
