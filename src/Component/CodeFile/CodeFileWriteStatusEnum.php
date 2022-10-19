<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Component\CodeFile;

enum CodeFileWriteStatusEnum: string
{
    /**
     * The code file is new.
     */
    case CREATED = 'created';
    /**
     * The code file already exists, and the new one may need to overwrite it.
     */
    case OVERWROTE = 'overwrote';
    /**
     * The new code file and the existing one are identical.
     */
    case SKIPPED = 'skipped';
    /**
     * The new code file and the existing one are identical.
     */
    case ERROR = 'error';
}
