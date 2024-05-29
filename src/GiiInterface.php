<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Yiisoft\Yii\Gii\Exception\GeneratorNotFoundException;

interface GiiInterface
{
    /**
     * @psalm-param GeneratorInterface $generator
     */
    public function addGenerator(GeneratorInterface $generator): void;

    /**
     * @throws GeneratorNotFoundException
     */
    public function getGenerator(string $id): GeneratorInterface;

    /**
     * @return GeneratorInterface[]|GeneratorProxy[]
     */
    public function getGenerators(): array;
}
