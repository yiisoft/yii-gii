<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests;

use Yiisoft\Validator\RuleHandlerResolver\RuleHandlerContainer;
use Yiisoft\Validator\RuleHandlerResolverInterface;
use Yiisoft\Yii\Gii\GiiServiceProvider;

final class GiiServiceProviderTest extends TestCase
{
    public function testDefinitions(): void
    {
        $provider = new GiiServiceProvider();

        $this->assertSame(
            [RuleHandlerResolverInterface::class => RuleHandlerContainer::class],
            $provider->getDefinitions(),
        );
    }

    public function testExtensions(): void
    {
        $provider = new GiiServiceProvider();

        $this->assertSame([], $provider->getExtensions());
    }
}
