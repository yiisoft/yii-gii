<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Closure;
use Yiisoft\Yii\Gii\Exception\GeneratorNotFoundException;

/**
 * @psalm-type LazyGenerator = Closure(): GeneratorInterface
 */
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
     * @return GeneratorInterface[]
     */
    public function getGenerators(): array;
}
