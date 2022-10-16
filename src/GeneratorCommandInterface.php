<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Yiisoft\Validator\Result;
use Yiisoft\Yii\Gii\Generator\AbstractGeneratorCommand;

interface GeneratorCommandInterface
{
    public static function getAttributeLabels(): array;

    public static function hints(): array;
}
