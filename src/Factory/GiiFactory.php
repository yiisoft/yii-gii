<?php

namespace Yiisoft\Yii\Gii\Factory;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Gii\Gii;
use Yiisoft\Yii\Gii\GiiInterface;
use Yiisoft\Yii\Gii\Parameters;

final class GiiFactory
{
    private array $generators;

    public function __construct(array $generators = [])
    {
        $this->generators = $generators;
    }

    public function __invoke(ContainerInterface $container): GiiInterface
    {
        $generators = array_merge(
            $container->get(Parameters::class)->get('gii.generators'),
            $this->generators
        );
        $generatorsInstances = [];

        foreach ($generators as $name => $generator) {
            if (!is_string($name)) {
                throw new InvalidArgumentException("Generator name must be set.");
            }
            $generatorsInstances[$name] = $container->get($generator);
        }
        return new Gii($generatorsInstances, $container);
    }
}
