<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Exception;

final class CodeFileWriter
{
    /**
     * Saves the generated code into files.
     *
     * @param CodeFile[] $files the code files to be saved
     * @param string[] $answers
     *
     * @return CodeFileWriteResult whether files are successfully saved without any error.
     */
    public function write(array $files, array $answers): CodeFileWriteResult
    {
        $result = new CodeFileWriteResult();

        foreach ($files as $file) {
            $fileId = $file->getId();
            $operation = $answers[$fileId];

            switch (CodeFileWriteOperationEnum::tryFrom($operation)) {
                case CodeFileWriteOperationEnum::OP_SKIP:
                    $result->addResult($file, CodeFileWriteStatusEnum::SKIPPED);
                    break;
                case CodeFileWriteOperationEnum::OP_CREATE:
                case CodeFileWriteOperationEnum::OP_OVERWRITE:
                    try {
                        $status = $file->save();
                    } catch (Exception $e) {
                        $result->addError(
                            $file,
                            $e->getMessage()
                        );
                        break;
                    }

                    $result->addResult($file, $status);
                    break;
                default:
                    $result->addError(
                        $file,
                        sprintf(
                            'Unknown operation "%s". Only the following operations are available: %s',
                            $operation,
                            implode(
                                ', ',
                                array_map(
                                    fn (CodeFileWriteOperationEnum $value) => $value->value,
                                    CodeFileWriteOperationEnum::cases()
                                )
                            ),
                        ),
                    );
                    break;
            }
        }

        return $result;
    }
}
