<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Yiisoft\Yii\Gii\Component\CodeFile\CodeFile;
use Yiisoft\Yii\Gii\Exception\InvalidGeneratorCommandException;
use Yiisoft\Yii\Gii\Generator\AbstractGeneratorCommand;

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
}
