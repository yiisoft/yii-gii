<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Tests;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Definitions\Reference;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\EventDispatcher\Dispatcher\Dispatcher;
use Yiisoft\EventDispatcher\Provider\Provider;
use Yiisoft\Files\FileHelper;
use Yiisoft\Translator\Translator;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\RuleHandlerResolver\RuleHandlerContainer;
use Yiisoft\Validator\RuleHandlerResolverInterface;
use Yiisoft\Validator\Validator;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\Gii\Generator as Generators;
use Yiisoft\Yii\Gii\Gii;
use Yiisoft\Yii\Gii\GiiInterface;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private ?ContainerInterface $container = null;

    protected function setUp(): void
    {
        parent::setUp();
        FileHelper::ensureDirectory(__DIR__ . '/runtime');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        FileHelper::removeDirectory(__DIR__ . '/runtime');
        $this->container = null;
    }

    protected function getContainer(array $definitions = []): ContainerInterface
    {
        if ($this->container === null) {
            $config = ContainerConfig::create()
                ->withDefinitions([
                    GiiInterface::class => function (ContainerInterface $container) {
                        $generators = [
                            Generators\Controller\Generator::getId() => Generators\Controller\Generator::class,
                            Generators\ActiveRecord\Generator::getId() => Generators\ActiveRecord\Generator::class,
                        ];
                        $generatorsInstances = [];
                        foreach ($generators as $class) {
                            $generatorsInstances[] = $container->get($class);
                        }
                        return new Gii($generatorsInstances);
                    },
                    Aliases::class => new Aliases(
                        [
                            '@src' => __DIR__,
                            '@views' => '@src/runtime',
                            '@view' => '@src/runtime',
                            '@root' => '@src/runtime',
                        ]
                    ),
                    EventDispatcherInterface::class => Dispatcher::class,
                    ListenerProviderInterface::class => Provider::class,
                    LoggerInterface::class => NullLogger::class,
                    TranslatorInterface::class => [
                        'class' => Translator::class,
                        '__construct()' => [
                            'locale' => 'en',
                            'fallbackLocale' => 'en',
                            'eventDispatcher' => Reference::to(EventDispatcherInterface::class),
                        ],
                    ],
                    RuleHandlerResolverInterface::class => RuleHandlerContainer::class,
                    ValidatorInterface::class => Validator::class,
                    ...$definitions,
                ]);
            $this->container = new Container($config);
        }
        return $this->container;
    }
}
