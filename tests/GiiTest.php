<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests;

use Yiisoft\Yii\Gii\Exception\GeneratorNotFoundException;
use Yiisoft\Yii\Gii\GiiInterface;

/**
 * GiiTestCase is the base class for all gii related test cases
 */
final class GiiTest extends TestCase
{
    public function testUnknownGeneratorInstance(): void
    {
        $this->expectException(GeneratorNotFoundException::class);
        $this->getContainer()->get(GiiInterface::class)->getGenerator('unknown');
    }
}
