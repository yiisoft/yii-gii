<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator\Controller;

use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Yii\Gii\Generator\AbstractGeneratorDTO;

class ControllerDTO extends AbstractGeneratorDTO
{
    #[Required()]
    #[Regex(
        pattern: '/^[A-Z][\w]*Controller$/',
        message: 'Only word characters are allowed, and the class name must start with a capital letter and end with "Controller".'
    )]
    #[Callback(['validateNewClass'])]
    private $controllerClass = [];

    #[Regex(
        pattern: '/^[\w\\\\]*$/',
        message: 'Only word characters and backslashes are allowed.',
        skipOnEmpty: true,
    )]
    private $baseClass = [];

    #[Regex(
        pattern: '/^[a-z][a-z0-9\\-,\\s]*$/',
        message: 'Only a-z, 0-9, dashes (-), spaces and commas are allowed.'
    )]
    private $actions = [];
}
