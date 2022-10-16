<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Request;

use Yiisoft\RequestModel\RequestModel;
use Yiisoft\Yii\Gii\Generator\Controller\ControllerGenerator;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\GiiInterface;

final class ControllerRequest extends RequestModel
{
    public function __construct(private GiiInterface $gii)
    {
    }

    public function getGenerator(): GeneratorInterface
    {
        /** @var ControllerGenerator $generator */
        return $this->gii->getGenerator(ControllerGenerator::getId());
    }

    public function getAnswers(): ?array
    {
        return $this->getAttributeValue('body.answers');
    }
}
