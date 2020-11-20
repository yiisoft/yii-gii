<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Yiisoft\Validator\ResultSet;

interface GeneratorInterface
{
    /**
     * Returns the name of the generator
     */
    public function getName(): string;

    /**
     * Returns the detailed description of the generator
     */
    public function getDescription(): string;

    /**
     * Generates the code based on the current user input and the specified code template files.
     * This is the main method that child classes should implement.
     * Please refer to {@see \Yiisoft\Yii\Gii\Generator\Controller\Generator::generate()} as an example
     * on how to implement this method.
     *
     * @return CodeFile[] a list of code files to be created.
     */
    public function generate(): array;

    /**
     * Returns the validation result
     */
    public function validate(): ResultSet;
}
