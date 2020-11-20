<?php

namespace Yiisoft\Yii\Gii\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Yiisoft\Composer\Config\Builder;
use Yiisoft\Di\Container;
use Yiisoft\Files\FileHelper;
use Yiisoft\Yii\Gii\Exception\GeneratorNotFoundException;
use Yiisoft\Yii\Gii\Generator\Controller\Generator as ControllerGenerator;
use Yiisoft\Yii\Gii\GiiInterface;

/**
 * GiiTestCase is the base class for all gii related test cases
 */
class GiiTestCase extends TestCase
{
    private ?ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();
        FileHelper::createDirectory(__DIR__ . '/runtime');
        $this->container = new Container(require Builder::path('tests', dirname(__DIR__)));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        FileHelper::removeDirectory(__DIR__ . '/runtime');
        $this->container = null;
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

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
