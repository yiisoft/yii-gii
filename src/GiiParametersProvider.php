<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

class GiiParametersProvider
{
    public function __construct(
        /**
         * @var string[] a list of available code templates. The array keys are the template names,
         * and the array values are the corresponding template paths or path aliases.
         */
        private array $templates = [],
    ) {
    }

    public function getTemplates(string $controllerId): array
    {
        $templates = $this->templates[$controllerId] ?? [];
        return array_merge(['default' => true], $templates);
    }

    public function getRequiredTemplates(): array
    {
        return [];
    }
}
