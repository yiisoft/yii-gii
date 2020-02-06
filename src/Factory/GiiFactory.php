<?php

namespace Yiisoft\Yii\Gii\Factory;


use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Gii\Gii;
use Yiisoft\Yii\Gii\Generators;
use Yiisoft\Yii\Gii\Parameters;

class GiiFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $generators = [];
        foreach (
            array_merge(
                $this->defaultGenerators(),
                $container->get(Parameters::class)->get('gii.generators', [])
            ) as $name => $generator
        ) {
            if (!is_string($name)) {
                throw new InvalidArgumentException();
            }
            $generators[$name] = new $generator($container->get(Aliases::class), $container->get(Parameters::class));
        }
        return new Gii($generators, $container);
    }

    private function defaultGenerators(): array
    {
        return [
            'controller' => Generators\Controller\Generator::class,
            //'form' => new Generators\Form\Generator(),
            //'module' => new Generators\Module\Generator(),
            //'extension' => new Generators\Extension\Generator(),
            //'crud' => new Generators\Crud\Generator,
            //'model' => new Generators\Model\Generator,
        ];
    }
}
