<?php

namespace Yiisoft\Yii\Gii\Factory;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Gii\Generators;
use Yiisoft\Yii\Gii\Gii;
use Yiisoft\Yii\Gii\GiiInterface;
use Yiisoft\Yii\Gii\Parameters;

class GiiFactory
{
    private array $generators;

    public function __construct(array $generators = [])
    {
        $this->generators = $generators;
    }

    public function __invoke(ContainerInterface $container): GiiInterface
    {
        $generators = array_merge($this->defaultGenerators(), $this->generators);
        $generatorsInstances = [];

        foreach ($generators as $name => $generator) {
            if (!is_string($name)) {
                throw new InvalidArgumentException();
            }
            $generatorsInstances[$name] = new $generator(
                $container->get(Aliases::class),
                $container->get(Parameters::class)
            );
        }
        return new Gii($generatorsInstances, $container);
    }

    private function defaultGenerators(): array
    {
        return [
            'controller' => Generators\Controller\AbstractGenerator::class,
            //'form' => new Generators\Form\Generator(),
            //'module' => new Generators\Module\Generator(),
            //'extension' => new Generators\Extension\Generator(),
            //'crud' => new Generators\Crud\Generator,
            //'model' => new Generators\Model\Generator,
        ];
    }
}
