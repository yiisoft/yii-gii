<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Closure;
use Yiisoft\Yii\Gii\Exception\GeneratorNotFoundException;

/**
 * @psalm-import-type LazyGenerator from GiiInterface
 */
final class Gii implements GiiInterface
{
    /**
     * @param array<string, GeneratorInterface|LazyGenerator> $generators
     */
    public function __construct(private array $generators)
    {
    }

    public function addGenerator(GeneratorInterface $generator): void
    {
        $this->generators[$generator::getId()] = $generator;
    }

    public function getGenerator(string $id): GeneratorInterface
    {
        if (!isset($this->generators[$id])) {
            throw new GeneratorNotFoundException('Generator "' . $id . '" not found');
        }

        return $this->generators[$id] instanceof Closure ? $this->generators[$id]() : $this->generators[$id];
    }

    public function getGenerators(): array
    {
        return array_map(
            fn (Closure|GeneratorInterface $generator) => $generator instanceof Closure ? $generator() : $generator,
            $this->generators
        );
    }
}
