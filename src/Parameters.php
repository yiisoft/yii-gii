<?php

namespace Yiisoft\Yii\Gii;

use Yiisoft\Arrays\ArrayHelper;

final class Parameters
{
    private array $parameters;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function get(string $name, $default = null)
    {
        return ArrayHelper::getValue($this->parameters, $name, $default);
    }
}
