<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\EventDispatcher\Dispatcher\Dispatcher;
use Yiisoft\EventDispatcher\Provider\Provider;
use Yiisoft\Files\FileHelper;
use Yiisoft\View\View;

/**
 * GiiTestCase is the base class for all gii related test cases
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    private ?ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();
        FileHelper::ensureDirectory(__DIR__ . '/runtime');

        $config = ContainerConfig::create()
            ->withDefinitions([
                \Yiisoft\Yii\Gii\GiiInterface::class => new \Yiisoft\Yii\Gii\Factory\GiiFactory(
                    [
                        'controller' => \Yiisoft\Yii\Gii\Generator\Controller\Generator::class,
                    ]
                ),
                Aliases::class => new Aliases(
                    [
                        '@app' => dirname(__DIR__) . '/tests',
                        '@views' => dirname(__DIR__) . '/tests/runtime',
                        '@view' => dirname(__DIR__) . '/tests/runtime',
                        '@root' => dirname(__DIR__) . '/tests/runtime',
                    ]
                ),
                EventDispatcherInterface::class => Dispatcher::class,
                ListenerProviderInterface::class => Provider::class,
                LoggerInterface::class => NullLogger::class,
                View::class => [
                    '__construct()' => [
                        'basePath' => '@views',
                    ],
                ],
            ]);
        $this->container = new Container($config);
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
}
