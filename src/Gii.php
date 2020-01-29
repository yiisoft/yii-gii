<?php

namespace Yiisoft\Yii\Gii;

use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Gii\Generators;

final class Gii implements GiiInterface
{
    private ContainerInterface $container;
    /**
     * @var array the list of IPs that are allowed to access this module.
     * Each array element represents a single IP filter which can be either an IP address
     * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
     * The default value is `['127.0.0.1', '::1']`, which means the module can only be accessed
     * by localhost.
     */
    private array $allowedIPs = ['127.0.0.1', '::1'];
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->generators = $this->defaultGenerators();
    }

    public function addGenerator(string $name, $generator): void
    {
        $this->generators[$name] = $generator;
    }

    public function getGenerator(string $name): GeneratorInterface
    {
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
