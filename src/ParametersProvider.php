<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

final class ParametersProvider
{
    public function __construct(
        /**
         * @var array<string, array<string, string>> a list of available code templates. The array keys are the template names,
         * and the array values are the corresponding template paths or path aliases.
         */
        private readonly array $templates = [],
    ) {
    }

    public function getTemplates(string $generator): array
    {
        $templates = $this->templates[$generator] ?? [];
        return ['default' => true, ...$templates];
    }
}
