<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator;

use Yiisoft\Yii\Gii\GeneratorCommandInterface;

class CommandHydrator
{
    public function __construct()
    {
    }

    /**
     * @psalm-param class-string<GeneratorCommandInterface> $commandClass
     * @param array $parameters
     * @return GeneratorCommandInterface
     */
    public function hydrate(string $commandClass, array $parameters): GeneratorCommandInterface
    {
        return new $commandClass(...$parameters);
    }
}
