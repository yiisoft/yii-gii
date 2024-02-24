<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Request;

use Yiisoft\Hydrator\Temp\RouteArgument;
use Yiisoft\Input\Http\Attribute\Parameter\Body;
use Yiisoft\Input\Http\RequestInputInterface;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\GiiInterface;

final class GeneratorRequest implements RequestInputInterface
{
    #[RouteArgument('generator')]
    private string $generatorId = '';

    #[Body('answers')]
    private array $answers = [];

    #[Body('parameters')]
    private array $parameters = [];

    public function __construct(private readonly GiiInterface $gii)
    {
    }

    public function getGenerator(): GeneratorInterface
    {
        return $this->gii->getGenerator($this->generatorId);
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function getBody(): array
    {
        return $this->parameters;
    }
}
