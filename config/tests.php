<?php

use Yiisoft\Aliases\Aliases;

return [
    \Yiisoft\Yii\Gii\GiiInterface::class => new \Yiisoft\Yii\Gii\Factory\GiiFactory(
        [
            'controller' => \Yiisoft\Yii\Gii\Generator\Controller\Generator::class
        ]
    ),
    \Yiisoft\Yii\Gii\Parameters::class => new \Yiisoft\Yii\Gii\Parameters(
        [
            'gii' => [
                'basePath' => '@root',
                'viewPath' => '@views',
                'controller' => [
                    'namespace' => 'App\\Controller',
                    'directory' => '@app/Controller',
                ],
            ],
        ]
    ),
    Yiisoft\Aliases\Aliases::class => new Aliases(
        [
            '@app' => dirname(__DIR__) . '/tests',
            '@views' => dirname(__DIR__) . '/tests/runtime',
            '@view' => dirname(__DIR__) . '/tests/runtime',
            '@root' => dirname(__DIR__) . '/tests/runtime'
        ]
    )
];
