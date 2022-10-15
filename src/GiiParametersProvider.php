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

    public function getTemplates(): array
    {
        return $this->templates;
    }

    //TODO: fix
    public function getRequiredTemplates(): array
    {
        return $this->templates;
    }
}
