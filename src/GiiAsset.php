<?php
namespace Yiisoft\Yii\Gii;

use Yiisoft\Assets\AssetBundle;

/**
 * This declares the asset files required by Gii.
 */
class GiiAsset extends AssetBundle
{
    public ?string $sourcePath = '@Yiisoft/Yii/Gii/assets';
    public array $css = [
        'css/main.css',
    ];
    public array $js = [
        'js/bs4-native.min.js',
        'js/gii.js',
    ];
    public array $depends = [
        \Yiisoft\Yii\JQuery\YiiAsset::class,
    ];
}
