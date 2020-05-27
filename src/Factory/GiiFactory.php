<?php

namespace Yiisoft\Yii\Gii\Factory;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Gii\Gii;
use Yiisoft\Yii\Gii\GiiInterface;

final class GiiFactory
{
    private array $generators;
    private array $params;

    public function __construct(array $generators = [], array $params = [])
    {
        $this->generators = $generators;
        $this->params = $params;
    }

    public function __invoke(ContainerInterface $container): GiiInterface
    {
        $generatorsInstances = [];
        foreach ($this->generators as $name => $generator) {
            if (!is_string($name)) {
                throw new InvalidArgumentException('Generator name must be set.');
            }
            $generatorsInstances[$name] = $container->get($generator);
        }
        return new Gii($generatorsInstances, $container, $this->params);
    }
}
