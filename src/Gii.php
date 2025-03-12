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
     * @param string $id
     * @return GeneratorInterface
     * @throws GeneratorNotFoundException
     */
    #[\Override]
    public function getGenerator(string $id): GeneratorInterface
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        
        $proxies = $this->proxies;
        $proxy = $proxies[$id] instanceof GeneratorProxy ? $proxies[$id] : [];   
        if (!empty($proxy)) {
            return $proxy->loadGenerator();
        }

        throw new GeneratorNotFoundException('Generator "' . $id . '" not found');
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
