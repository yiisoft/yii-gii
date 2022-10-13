<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Request;

use Yiisoft\RequestModel\RequestModel;
use Yiisoft\Yii\Gii\Generator\AbstractGenerator;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\GiiInterface;

final class GeneratorRequest extends RequestModel
{
    public function __construct(private GiiInterface $gii)
    {
    }

    public function getGenerator(): GeneratorInterface
    {
        /** @var AbstractGenerator $generator */
        $generator = $this->gii->getGenerator($this->getAttributeValue('router.generator'));

        $generator->loadStickyAttributes();
        $generator->load((array)$this->getAttributeValue('body'));

        return $generator;
    }

    public function getAnswers(): ?array
    {
        return $this->getAttributeValue('body.answers');
    }
}
