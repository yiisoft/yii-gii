<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Yiisoft\Injector\Injector;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\Gii;
use Yiisoft\Yii\Gii\GiiInterface;

/**
 * @var array $params
 */

return [
    GiiInterface::class => function (Injector $injector) use ($params): GiiInterface {
        $generatorsInstances = [];
        $generators = $params['yiisoft/yii-gii']['generators'];

        foreach ($generators as $generator) {
            /**
             * @var $generator GeneratorInterface
             */
            $class = $generator['class'];
            $generator = $injector->make($class, $generator['parameters']);
            $generatorsInstances[] = $generator;
        }
        return new Gii($generatorsInstances);
    },
];
