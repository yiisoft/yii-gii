<?php

namespace Yiisoft\Yii\Gii\Factory;


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
        foreach ($this->defaultGenerators() as $name => $generator) {
            $generators[$name] = new $generator($container->get(Aliases::class), $container->get(Parameters::class));
        }
        return new Gii($generators, $container);
    }

    private function defaultGenerators()
    {
        return [
            //'model' => new Generators\Model\Generator,
            //'crud' => new Generators\Crud\Generator,
            'controller' => Generators\Controller\Generator::class,
            //'form' => new Generators\Form\Generator(),
            //'module' => new Generators\Module\Generator(),
            //'extension' => new Generators\Extension\Generator(),
        ];
    }
}
