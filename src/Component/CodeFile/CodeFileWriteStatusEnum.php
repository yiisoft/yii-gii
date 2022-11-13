<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Component\CodeFile;

enum CodeFileWriteStatusEnum: string
{
    /**
     * The code file was created.
     */
    case CREATED = 'created';
    /**
     * The code file was overwrote.
     */
    case OVERWROTE = 'overwrote';
    /**
     * The new code file generation was skipped for some reasons.
     */
    case SKIPPED = 'skipped';
    /**
     * The new code file was not generated because of an error.
     */
    case ERROR = 'error';
}
