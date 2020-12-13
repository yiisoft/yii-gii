<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests;

use Yiisoft\Yii\Gii\Exception\GeneratorNotFoundException;
use Yiisoft\Yii\Gii\Generator\Controller\Generator as ControllerGenerator;
use Yiisoft\Yii\Gii\GiiInterface;

/**
 * GiiTestCase is the base class for all gii related test cases
 */
class GiiTest extends TestCase
{
    public function testGeneratorInstance(): void
    {
        $controllerGenerator = $this->getContainer()->get(GiiInterface::class)->getGenerator('controller');
        $this->assertInstanceOf(ControllerGenerator::class, $controllerGenerator);
    }

    public function testUnknownGeneratorInstance(): void
    {
        $this->expectException(GeneratorNotFoundException::class);
        $this->getContainer()->get(GiiInterface::class)->getGenerator('unknown');
    }

    public function testWrongGeneratorInstance(): void
    {
        $this->getContainer()->get(GiiInterface::class)->addGenerator('wrong', new \stdClass());
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Generator should be GeneratorInterface instance. "' . get_class(new \stdClass()) . '" given.'
        );
        $this->getContainer()->get(GiiInterface::class)->getGenerator('wrong');
    }
}
