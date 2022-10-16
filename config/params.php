<?php

declare(strict_types=1);

use Yiisoft\Yii\Gii\Command\ControllerCommand;
use Yiisoft\Yii\Gii\Generator\Controller\ControllerGenerator;

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
                'class' => ControllerGenerator::class,
                'parameters' => [
                    'namespace' => 'App\\Controller',
                    'directory' => '@src/Controller',
                ],
            ],
        ],
        'basePath' => '@root',
        'viewPath' => '@views',
    ],
];
