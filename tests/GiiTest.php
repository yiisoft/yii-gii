<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests;

use Psr\Container\ContainerInterface;
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
        $gii = $this->getContainer()->get(GiiInterface::class);
        $controllerGenerator = $gii->getGenerator('controller');
        $this->assertInstanceOf(ControllerGenerator::class, $controllerGenerator);

        $gii->addGenerator('stringGenerator', ControllerGenerator::class);
        $controllerGenerator = $gii->getGenerator('stringGenerator');
        $this->assertInstanceOf(ControllerGenerator::class, $controllerGenerator);

        $gii->addGenerator('callableGenerator', new class () {
            public function __invoke(ContainerInterface $container)
            {
                return $container->get(ControllerGenerator::class);
            }
        });
        $controllerGenerator = $gii->getGenerator('callableGenerator');
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
            'Generator should be GeneratorInterface instance. "' . \stdClass::class . '" given.'
        );
        $this->getContainer()->get(GiiInterface::class)->getGenerator('wrong');
    }

    public function testWrongGeneratorTypeInstance(): void
    {
        $this->getContainer()->get(GiiInterface::class)->addGenerator('wrongType', 409);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Generator should be GeneratorInterface instance. "' . get_debug_type(409) . '" given.'
        );
        $this->getContainer()->get(GiiInterface::class)->getGenerator('wrongType');
    }
}
