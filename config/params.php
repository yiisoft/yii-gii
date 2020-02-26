<?php

use Yiisoft\Yii\Gii\Command\ControllerCommand;

return [
    'console' => [
        'commands' => [
            'gii/controller' => ControllerCommand::class,
        ],
    ],
    'gii'     => [
        'generators' => [
            'controller' => \Yiisoft\Yii\Gii\Generators\Controller\Generator::class
        ],
        'basePath'   => '@root',
        'viewPath'   => '@views',
        'controller' => [
            'namespace' => 'App\\Controller',
            'directory' => '@src/Controller',
        ],
    ],
];
