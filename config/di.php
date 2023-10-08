<?php

declare(strict_types=1);

use Yiisoft\Injector\Injector;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\Gii;
use Yiisoft\Yii\Gii\GiiInterface;
use Yiisoft\Yii\Gii\ParametersProvider;

/**
 * @var array $params
 */

return [
    GiiInterface::class => function (Injector $injector) use ($params): GiiInterface {
        $generatorsInstances = [];
        $generators = $params['yiisoft/yii-gii']['generators'];

        foreach ($generators as $generator) {
            $class = $generator['class'];
            /**
             * @var $loader Closure(): GeneratorInterface
             */
            $loader = fn() => $injector->make($class, $generator['parameters'] ?? []);
            $generatorsInstances[$class] = $loader;
        }
        return new Gii($generatorsInstances);
    },
    ParametersProvider::class => [
        'class' => ParametersProvider::class,
        '__construct()' => [
            'templates' => $params['yiisoft/yii-gii']['parameters']['templates'],
        ],
    ],
];
