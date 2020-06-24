<?php

declare(strict_types=1);

use Yiisoft\Yii\Gii\Command\ControllerCommand;

return [
    'console' => [
        'commands' => [
            'gii/controller' => ControllerCommand::class,
        ],
    ],
    'aliases' => [
        '@yiisoft/yii-gii' => dirname(__DIR__)
    ],
    'gii'     => [
        'generators' => [
            'controller' => \Yiisoft\Yii\Gii\Generator\Controller\Generator::class
        ],
        'basePath'   => '@root',
        'viewPath'   => '@views',
        'controller' => [
            'namespace' => 'App\\Controller',
            'directory' => '@src/Controller',
        ],
    ],
];
