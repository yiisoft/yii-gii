<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Exception;

use Exception;
use Yiisoft\Validator\Result;

final class InvalidGeneratorCommandException extends Exception
{
    public function __construct(private Result $result)
    {
        parent::__construct('Invalid generator data.');
    }

    public function getResult(): Result
    {
        return $this->result;
    }
}
