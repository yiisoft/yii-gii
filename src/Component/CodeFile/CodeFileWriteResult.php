<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Component\CodeFile;

/**
 * @psalm-type Results array<string, array{'id': string, 'status': string, 'error': null|string}>
 */
final class CodeFileWriteResult
{
    /**
     * @psalm-var Results
     */
    private array $results = [];

    public function addResult(CodeFile $file, CodeFileWriteStatusEnum $status): void
    {
        $this->results[$file->getId()] = [
            'id' => $file->getId(),
            'status' => $status->value,
            'error' => null,
        ];
    }

    public function addError(CodeFile $file, string $error): void
    {
        $this->results[$file->getId()] = [
            'id' => $file->getId(),
            'status' => CodeFileWriteStatusEnum::ERROR->value,
            'error' => $error,
        ];
    }

    /**
     * @psalm-return Results
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
