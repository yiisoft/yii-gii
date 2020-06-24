<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

interface GiiInterface
{
    public function addGenerator(string $name, $generator): void;

    public function getGenerator(string $name): GeneratorInterface;
}
