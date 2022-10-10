<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Yiisoft\Yii\Gii\Exception\GeneratorNotFoundException;

interface GiiInterface
{
    /**
     * @param string $name
     * @param mixed $generator
     */
    public function addGenerator(string $name, mixed $generator): void;

    /**
     * @throws GeneratorNotFoundException
     */
    public function getGenerator(string $name): GeneratorInterface;
}
