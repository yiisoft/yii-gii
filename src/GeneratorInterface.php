<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use ReflectionException;
use Yiisoft\Yii\Gii\Component\CodeFile\CodeFile;
use Yiisoft\Yii\Gii\Exception\InvalidConfigException;
use Yiisoft\Yii\Gii\Exception\InvalidGeneratorCommandException;

interface GeneratorInterface
{
    /**
     * Returns the id of the generator
     */
    public static function getId(): string;

    /**
     * Returns the name of the generator
     */
    public static function getName(): string;

    /**
     * Returns the detailed description of the generator
     */
    public static function getDescription(): string;

    /**
     * @psalm-return class-string<GeneratorCommandInterface>
     */
    public static function getCommandClass(): string;

    /**
     * @throws InvalidConfigException
     * @throws ReflectionException
     *
     * @return string the root path of the template files that are currently being used.
     */
    public function getTemplatePath(GeneratorCommandInterface $command): string;

    /**
     * Generates the code based on the current user input and the specified code template files.
     * This is the main method that child classes should implement.
     * Please refer to {@see \Yiisoft\Yii\Gii\Generator\Controller\ControllerGenerator::generate()} as an example
     * on how to implement this method.
     *
     * @throws InvalidGeneratorCommandException
     *
     * @return CodeFile[] a list of code files to be created.
     */
    public function generate(GeneratorCommandInterface $command): array;

    /**
     * Returns a list of code template files that are required.
     * Derived classes usually should override this method if they require the existence of
     * certain template files.
     *
     * @return array list of code template files that are required. They should be file paths
     * relative to {@see getTemplatePath()}.
     */
    public function getRequiredTemplates();
}
