<?php

declare(strict_types=1);

use Yiisoft\Yii\Gii\Command\ControllerCommand;

return [
    'yiisoft/yii-console' => [
        'commands' => [
            'gii/controller' => ControllerCommand::class,
        ],
    ],
    'yiisoft/yii-gii' => [
        'generators' => [
            'controller' => \Yiisoft\Yii\Gii\Generator\Controller\Generator::class,
        ],
        'basePath' => '@root',
        'viewPath' => '@views',
        'controller' => [
            'namespace' => 'App\\Controller',
            'directory' => '@src/Controller',
        ],
    ],
];
