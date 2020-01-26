<?php

namespace Yiisoft\Yii\Gii;


interface GiiInterface
{
    public function addGenerator(string $name, $generator);

    public function getGenerator(string $name);
}
