<?php

namespace Yiisoft\Yii\Gii\Exception;

use Exception;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class GeneratorNotFoundException extends Exception implements FriendlyExceptionInterface
{
    public function getName(): string
    {
        return 'Generator not found';
    }

    public function getSolution(): ?string
    {
        return "When you add a generator for the Gii Generator you should specify a value that can be:\n\n"
            . "- Name of the class implementing GeneratorInterface.\n"
            . "- An object implementing GeneratorInterface.\n"
            . "- A function that returns an object implementing GeneratorInterface.";
    }
}
