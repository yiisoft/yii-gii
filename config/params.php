<?php

use Yiisoft\Yii\Gii\Command\ControllerCommand;

return [
    'console' => [
        'commands' => [
            'gii/controller' => ControllerCommand::class,
        ],
    ],
    'gii'     => [
        'basePath'   => '@root',
        'viewPath'   => '@views',
        'controller' => [
            'namespace' => 'App\\Controller',
            'directory' => '@src/Controller',
        ],
    ],
];
