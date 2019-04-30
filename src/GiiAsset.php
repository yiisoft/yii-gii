<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yiisoft\Yii\Gii;

use yii\web\AssetBundle;

/**
 * This declares the asset files required by Gii.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GiiAsset extends AssetBundle
{
    public $sourcePath = '@yii/gii/assets';
    public $css = [
        'main.css',
    ];
    public $js = [
        'gii.js',
    ];
    public $depends = [
        \yii\web\YiiAsset::class,
        \yii\bootstrap\BootstrapAsset::class,
        \yii\bootstrap\BootstrapPluginAsset::class,
        \Yiisoft\Yii\Gii\TypeAheadAsset::class,
    ];
}
