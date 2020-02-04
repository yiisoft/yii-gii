<?php

namespace Yiisoft\Yii\Gii;

use Psr\Container\ContainerInterface;
use Yiisoft\Factory\Exceptions\NotFoundException;
use Yiisoft\Yii\Gii\Exception\GeneratorNotFoundException;
use Yiisoft\Yii\Gii\Generators\Controller\Generator;

final class Gii implements GiiInterface
{
    private ContainerInterface $container;
    /**
     * @var GeneratorInterface[] a list of generator configurations or instances. The array keys
     * are the generator IDs (e.g. "crud"), and the array elements are the corresponding generator
     * configurations or the instances.
     *
     * After the module is initialized, this property will become an array of generator instances
     * which are created based on the configurations previously taken by this property.
     *
     * Newly assigned generators will be merged with the [[coreGenerators()|core ones]], and the former
     * takes precedence in case when they have the same generator ID.
     */
    private array $generators;

    public function __construct(array $generators, ContainerInterface $container)
    {
        $this->container  = $container;
        $this->generators = $generators;
    }

    public function addGenerator(string $name, $generator): void
    {
        $this->generators[$name] = $generator;
    }

    /**
     * @param  string  $name
     * @return GeneratorInterface
     * @throws NotFoundException
     * @throws GeneratorNotFoundException
     */
    public function getGenerator(string $name): GeneratorInterface
    {
        if (!isset($this->generators[$name])) {
            throw new GeneratorNotFoundException('Generator "'.$name.'" not found');
        }
        $generator = $this->generators[$name];
        if (is_string($generator)) {
            $generator = $this->container->get($generator);
        } elseif (is_object($generator) && $generator instanceof GeneratorInterface) {
            return $generator;
        } elseif (is_object($generator) && method_exists($generator, '__invoke')) {
            $generator = $generator($this->container);
        }
        if ($generator instanceof GeneratorInterface) {
            return $generator;
        }
        throw new \RuntimeException(); // TODO: better exception
    }

    /**
     * Returns the list of the core code generator configurations.
     * @return array the list of the core code generator configurations.
     */
    private function defaultGenerators()
    {
        return [
            'model' => Generators\Model\Generator::class,
            'crud' => Generators\Crud\Generator::class,
            'controller' => Generators\Controller\Generator::class,
            'form' => Generators\Form\Generator::class,
            'module' => Generators\Module\Generator::class,
            'extension' => Generators\Extension\Generator::class,
        ];
    }

}
