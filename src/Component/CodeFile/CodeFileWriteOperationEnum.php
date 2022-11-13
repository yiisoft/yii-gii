<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Component\CodeFile;

enum CodeFileWriteOperationEnum: string
{
    /**
     * The code file is new.
     */
    case SAVE = 'save';
    /**
     * The new code file and the existing one are identical.
     */
    case SKIP = 'skip';

    /**
     * Operations map to be performed.
     */
    public static function getLabels(): array
    {
        return[
            self::SAVE->value => 'Save',
            self::SKIP->value => 'Skip',
        ];
    }
}
