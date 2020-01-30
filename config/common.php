<?php
/**
 * @var $params
 */

return [
    \Yiisoft\Yii\Gii\GiiInterface::class => new \Yiisoft\Yii\Gii\Factory\GiiFactory(),
    \Yiisoft\Yii\Gii\Parameters::class   => static function () use (&$params) {
        return new \Yiisoft\Yii\Gii\Parameters($params);
    },
];
