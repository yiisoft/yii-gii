<?php

declare(strict_types=1);

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
            $generator = $container->get($generator);
            if (array_key_exists($name, $this->params) && is_array($this->params[$name])) {
                $generator->load($this->params[$name]);
            }
            $generatorsInstances[$name] = $generator;
        }
        return new Gii($generatorsInstances, $container);
    }
}
