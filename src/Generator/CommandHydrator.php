<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator;

use Yiisoft\Yii\Gii\GeneratorCommandInterface;

class CommandHydrator
{
    public function __construct()
    {
    }

    public function hydrate(string $commandClass, array $parameters): GeneratorCommandInterface
    {
        return new $commandClass(...$parameters);
    }
}
