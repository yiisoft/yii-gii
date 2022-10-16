<?php

declare(strict_types=1);

use Yiisoft\Yii\Gii\Command\ControllerCommand;
use Yiisoft\Yii\Gii\Generator\Controller\ControllerGenerator;

return [
    'yiisoft/yii-console' => [
        'commands' => [
            'gii/controller' => ControllerCommand::class,
        ],
    ],
    'yiisoft/yii-gii' => [
        'enabled' => true,
        'allowedIPs' => ['127.0.0.1', '::1'],
        'generators' => [
            'controller' => ControllerGenerator::class,
        ],
        'basePath' => '@root',
        'viewPath' => '@views',
        'controller' => [
            'namespace' => 'App\\Controller',
            'directory' => '@src/Controller',
        ],
    ],
];
