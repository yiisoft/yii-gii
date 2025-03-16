<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Yiisoft\Yii\Gii\Exception\GeneratorNotFoundException;

final class Gii implements GiiInterface
{
    /**
     * @param array<string, GeneratorProxy> $proxies
     * @param array<string, GeneratorInterface> $instances
     */
    public function __construct(
        private readonly array $proxies,
        private array $instances,
    ) {
    }

    public function addGenerator(GeneratorInterface $generator): void
    {
        $this->instances[$generator::getId()] = $generator;
    }

    public function getGenerator(string $id): GeneratorInterface
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!isset($this->proxies[$id])) {
            throw new GeneratorNotFoundException('Generator "' . $id . '" not found');
        }

        return $this->instances[$id] = $this->proxies[$id]->loadGenerator();
    }

    public function getGenerators(): array
    {
        return [
            ...$this->instances,
            ...$this->proxies,
        ];
    }
}
