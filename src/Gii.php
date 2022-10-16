<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Yiisoft\Yii\Gii\Exception\GeneratorNotFoundException;

final class Gii implements GiiInterface
{
    /**
     * @param array<string, GeneratorInterface> $generators
     */
    public function __construct(private array $generators)
    {
        $this->generators = array_combine(
            array_map(fn (GeneratorInterface $generator) => $generator::getId(), $generators),
            array_values($this->generators)
        );
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

        return $this->generators[$id];
    }
}
