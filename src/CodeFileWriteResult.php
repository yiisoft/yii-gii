<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use JetBrains\PhpStorm\ArrayShape;

class CodeFileWriteResult
{
    private array $results = [];

    public function addResult(CodeFile $file, CodeFileWriteStatusEnum $status): void
    {
        $this->results[$file->getId()] = [
            'status' => $status->value,
            'error' => null,
        ];
    }

    public function addError(CodeFile $file, string $error): void
    {
        $this->results[$file->getId()] = [
            'status' => CodeFileWriteStatusEnum::ERROR->value,
            'error' => $error,
        ];
    }

    #[ArrayShape([
        'status' => 'string',
        'error' => 'null|string',
    ])]
    public function getResults(): array
    {
        return $this->results;
    }
}
