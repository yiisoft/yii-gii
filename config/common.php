<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\Gii;
use Yiisoft\Yii\Gii\GiiInterface;

/**
 * @var array $params
 */

return [
    GiiInterface::class => function (ContainerInterface $container) use ($params): GiiInterface {
        $generatorsInstances = [];
        $generators = $params['yiisoft/yii-gii']['generators'];
        $generatorsParameters = $params['yiisoft/yii-gii'];

        foreach ($generators as $name => $generator) {
            if (!is_string($name)) {
                throw new InvalidArgumentException('Generator name must be set.');
            }

            /**
             * TODO: fix preparing generators
             * @var $generator GeneratorInterface
             */
            $generator = $container->get($generator);
            if (array_key_exists($name, $generatorsParameters) && is_array($generatorsParameters[$name])) {
                $generator->load($generatorsParameters[$name]);
            }
            $generatorsInstances[$name] = $generator;
        }
        return new Gii($generatorsInstances, $container);
    },
];
