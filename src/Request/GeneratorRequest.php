<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Request;

use Yiisoft\RequestModel\RequestModel;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\GiiInterface;

final class GeneratorRequest extends RequestModel
{
    public function __construct(private readonly GiiInterface $gii)
    {
    }

    public function getGenerator(): GeneratorInterface
    {
        return $this->gii->getGenerator($this->getAttributeValue('router.generator'));
    }

    public function getAnswers(): array
    {
        return $this->getAttributeValue('body.answers', []);
    }

    public function getBody(): array
    {
        return $this->getAttributeValue('body.parameters', []);
    }
}
