<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Yiisoft\Yii\Gii\Exception\GeneratorNotFoundException;

/**
 * @psalm-import-type LazyGenerator from GiiInterface
 */
final class Gii implements GiiInterface
{
    /**
     * @param array<string, GeneratorInterface|GeneratorProxy> $proxies
     */
    public function __construct(private array $proxies)
    {
    }

    public function addGenerator(GeneratorInterface $generator): void
    {
        $this->proxies[$generator::getId()] = new GeneratorProxy(fn () => $generator, $generator::class);
    }

    public function getGenerator(string $id): GeneratorInterface
    {
        return $this->proxies[$id]?->loadGenerator() ?? throw new GeneratorNotFoundException('Generator "' . $id . '" not found');
    }

    public function getGenerators(): array
    {
        return $this->proxies;
    }
}
