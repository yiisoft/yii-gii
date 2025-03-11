<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Yiisoft\Yii\Gii\Exception\GeneratorNotFoundException;

final class Gii implements GiiInterface
{
    /**
     * @param array<string, GeneratorInterface|GeneratorProxy> $proxies
     * @param array<string, GeneratorInterface> $instances
     */
    public function __construct(
        private readonly array $proxies,
        private array $instances,
    ) {
    }

    #[\Override]
    public function addGenerator(GeneratorInterface $generator): void
    {
        $this->instances[$generator::getId()] = $generator;
    }

    /** 
     * @psalm-suppress PossiblyUndefinedMethod $proxy->loadGenerator() 
     */
    #[\Override]
    public function getGenerator(string $id): GeneratorInterface
    {
        return $this->instances[$id] ?? (isset($this->proxies[$id])
            ? $this->proxies[$id]->loadGenerator()
            : throw new GeneratorNotFoundException('Generator "' . $id . '" not found'));
    }

    #[\Override]
    public function getGenerators(): array
    {
        return [
            ...$this->instances,
            ...$this->proxies,
        ];
    }
}
