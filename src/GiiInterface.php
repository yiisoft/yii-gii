<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

interface GiiInterface
{
    /**
     * @param string $name
     * @param mixed $generator
     */
    public function addGenerator(string $name, $generator): void;

    public function getGenerator(string $name): GeneratorInterface;
}
