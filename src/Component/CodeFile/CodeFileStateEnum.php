<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Component\CodeFile;

enum CodeFileStateEnum: string
{
    /**
     * The code file is new.
     */
    case NOT_EXIST = 'not_exist';
    /**
     * The code file already exists, and the new one may need to overwrite it.
     */
    case PRESENT_DIFFERENT = 'present_different';
    /**
     * The new code file and the existing one are identical.
     */
    case PRESENT_SAME = 'present_same';
}
