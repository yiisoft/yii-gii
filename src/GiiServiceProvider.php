<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Yiisoft\Di\ServiceProviderInterface;
use Yiisoft\Validator\RuleHandlerResolver\RuleHandlerContainer;
use Yiisoft\Validator\RuleHandlerResolverInterface;

final class GiiServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            RuleHandlerResolverInterface::class => RuleHandlerContainer::class,
        ];
    }

    public function getExtensions(): array
    {
        return [];
    }
}
