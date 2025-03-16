<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Exception;

use Exception;
use Yiisoft\Validator\Result;

final class InvalidGeneratorCommandException extends Exception
{
    public function __construct(private readonly Result $result)
    {
        parent::__construct(
            sprintf(
                'Generator data validation failed: %s.',
                implode(", ", $this->result->getErrorMessages()),
            )
        );
    }

    public function getResult(): Result
    {
        return $this->result;
    }
}
