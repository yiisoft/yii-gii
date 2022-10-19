<?php

declare(strict_types=1);

use Yiisoft\Injector\Injector;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\Gii;
use Yiisoft\Yii\Gii\GiiInterface;
use Yiisoft\Yii\Gii\GiiParametersProvider;

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
             * @var $generator GeneratorInterface
             */
            $generator = $injector->make($class, $generator['parameters'] ?? []);
            $generatorsInstances[] = $generator;
        }
        return new Gii($generatorsInstances);
    },
    GiiParametersProvider::class => [
        'class' => GiiParametersProvider::class,
        '__construct()' => [
            'templates' => $params['yiisoft/yii-gii']['parameters']['templates']
        ]
    ]
];
