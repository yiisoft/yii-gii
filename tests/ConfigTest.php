<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests;

use Yiisoft\Validator\RuleHandlerResolver\RuleHandlerContainer;
use Yiisoft\Validator\RuleHandlerResolverInterface;

use function dirname;

final class ConfigTest extends TestCase
{
    public function testDiDefinitions(): void
    {
        $params = require dirname(__DIR__) . '/config/params.php';
        $definitions = (static fn(): array => require dirname(__DIR__) . '/config/di.php')();

        $this->assertSame(
            RuleHandlerContainer::class,
            $definitions[RuleHandlerResolverInterface::class],
        );
    }
}
