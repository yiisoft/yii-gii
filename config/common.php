<?php
/**
 * @var $params
 */

return [
    \Yiisoft\Yii\Gii\GiiInterface::class => static function ($container) {
        return new \Yiisoft\Yii\Gii\Factory\GiiFactory($container);
    },
    \Yiisoft\Yii\Gii\Parameters::class => static function () use ($params) {
        return new \Yiisoft\Yii\Gii\Parameters($params);
    },
];
