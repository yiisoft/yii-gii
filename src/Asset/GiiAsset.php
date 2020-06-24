<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Asset;

use Yiisoft\Assets\AssetBundle;
use Yiisoft\Yii\JQuery\YiiAsset;

/**
 * This declares the asset files required by Gii.
 */
class GiiAsset extends AssetBundle
{
    public ?string $sourcePath = '@yiisoft/yii-gii/assets';
    public array $css = [
        'css/main.css',
    ];
    public array $js = [
        'js/bs4-native.min.js',
        'js/gii.js',
    ];
    public array $depends = [
        YiiAsset::class,
    ];
}
