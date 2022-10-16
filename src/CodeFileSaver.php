<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Exception;
use ReflectionException;
use Yiisoft\Yii\Gii\Exception\InvalidConfigException;
use Yiisoft\Yii\Gii\Generator\AbstractGeneratorCommand;

class CodeFileSaver
{
    /**
     * Saves the generated code into files.
     *
     * @param CodeFile[] $files the code files to be saved
     * @param string[] $results this parameter receives a value from this method indicating the log messages
     * generated while saving the code files.
     *
     * @return bool whether files are successfully saved without any error.
     * @throws InvalidConfigException
     *
     * @throws ReflectionException
     */
    public function save(AbstractGeneratorCommand $command, array $files, array $answers, array &$results): bool
    {
        $results = ['Generating code using template "' . $command->getTemplate() . '"...'];
        $hasError = false;
        foreach ($files as $file) {
            $relativePath = $file->getRelativePath();
            if (!empty($answers[$file->getId()]) && $file->getOperation() !== CodeFile::OP_SKIP) {
                try {
                    $file->save();
                    $results[] = $file->getOperation() === CodeFile::OP_CREATE
                        ? " generated  $relativePath"
                        : " overwrote  $relativePath";
                } catch (Exception $e) {
                    $hasError = true;
                    $results[] = sprintf(
                        "   generating %s\n    - <span class=\"error\">%s</span>",
                        $relativePath,
                        $e->getMessage()
                    );
                }
            } else {
                $results[] = "   skipped    $relativePath";
            }
        }
        $results[] = 'done!';

        return !$hasError;
    }
}