<?php

declare(strict_types=1);

use Yiisoft\Yii\Gii\Command\ControllerCommand;
use Yiisoft\Yii\Gii\Generator as Generators;

return [
    'yiisoft/yii-debug' => [
        'ignoredRequests' => [
            '/gii**',
        ],
    ],
    'yiisoft/yii-swagger' => [
        'annotation-paths' => [
            dirname(__DIR__) . '/src/Controller',
        ],
    ],
    'yiisoft/yii-console' => [
        'commands' => [
            'gii/controller' => ControllerCommand::class,
        ],
    ],
    'yiisoft/yii-gii' => [
        'enabled' => true,
        'allowedIPs' => ['127.0.0.1', '::1'],
        'generators' => [
            [
                'class' => Generators\Controller\Generator::class,
                'parameters' => [],
            ],
            [
                'class' => Generators\ActiveRecord\Generator::class,
                'parameters' => [],
            ],
        ],
        'parameters' => [
            'templates' => [],
        ],
    ],
];
