<?php

namespace Yiisoft\Yii\Gii;

use Psr\Container\ContainerInterface;
use RuntimeException;
use Yiisoft\Yii\Gii\Exception\GeneratorNotFoundException;

final class Gii implements GiiInterface
{
    private ContainerInterface $container;
    private iterable $generators;

    public function __construct(iterable $generators, ContainerInterface $container)
    {
        $this->generators = $generators;
        $this->container = $container;
    }

    public function addGenerator(string $name, $generator): void
    {
        $this->generators[$name] = $generator;
    }

    /**
     * @param string $name
     * @return GeneratorInterface
     * @throws GeneratorNotFoundException
     */
    public function getGenerator(string $name): GeneratorInterface
    {
        if (!isset($this->generators[$name])) {
            throw new GeneratorNotFoundException('Generator "' . $name . '" not found');
        }
        $generator = $this->generators[$name];
        if (is_string($generator)) {
            $generator = $this->container->get($generator);
        } elseif ($generator instanceof GeneratorInterface) {
            return $generator;
        } elseif (is_object($generator) && method_exists($generator, '__invoke')) {
            $generator = $generator($this->container);
        }
        if (!($generator instanceof GeneratorInterface)) {
            throw new RuntimeException(
                'Generator should be GeneratorInterface instance. "' . get_class($generator) . '" given.'
            );
        }
        return $generator;
    }
}
