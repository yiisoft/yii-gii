<?php

namespace Yiisoft\Yii\Gii\Factory;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Factory\Factory;
use Yiisoft\Yii\Gii\Gii;
use Yiisoft\Yii\Gii\GiiInterface;
use Yiisoft\Yii\Gii\Parameters;

final class GiiFactory extends Factory
{
    private array $generators;

    public function __construct(ContainerInterface $container, array $generators = [], array $definitions = [])
    {
        $this->generators = $generators;
        parent::__construct($container, $definitions);
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
            $generatorsInstances[$name] = $this->create(
                $generator,
                [
                    $container->get(Aliases::class),
                    $container->get(Parameters::class)
                ]
            );
        }
        return new Gii($generatorsInstances, $container);
    }
}
