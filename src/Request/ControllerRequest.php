<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Request;

use Yiisoft\RequestModel\RequestModel;
use Yiisoft\Yii\Gii\Generator\AbstractGenerator;
use Yiisoft\Yii\Gii\Generator\Controller\ControllerGenerator;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\GiiInterface;

final class ControllerRequest extends RequestModel
{
    private const NAME = 'controller';

    public function __construct(private GiiInterface $gii)
    {
    }

    public function getGenerator(): GeneratorInterface
    {
        /** @var ControllerGenerator $generator */
        $generator = $this->gii->getGenerator(self::NAME);

        return $generator;
    }

    public function getAnswers(): ?array
    {
        return $this->getAttributeValue('body.answers');
    }
}
