<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

enum CodeFileWriteOperationEnum: string
{
    /**
     * The code file is new.
     */
    case OP_CREATE = 'create';
    /**
     * The code file already exists, and the new one may need to overwrite it.
     */
    case OP_OVERWRITE = 'overwrite';
    /**
     * The new code file and the existing one are identical.
     */
    case OP_SKIP = 'skip';
    /**
     * Operations map to be performed.
     */
    public static function getLabels(): array
    {
        return[
            self::OP_CREATE->value => 'Create',
            self::OP_OVERWRITE->value => 'Overwrite',
            self::OP_SKIP->value => 'Skip',
        ];
    }
}
