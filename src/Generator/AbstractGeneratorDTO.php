<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator;

use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\ValidationContext;

abstract class AbstractGeneratorDTO
{
    #[Required(message: 'A code template must be selected.')]
    #[Callback(['validateTemplate'])]
    private $template = [];

    /**
     * Validates the template selection.
     * This method validates whether the user selects an existing template
     * and the template contains all required template files as specified in {@see requiredTemplates()}.
     *
     * @param string $value
     */
    public function validateTemplate(mixed $value, Callback $rule, ValidationContext $validationContext): Result
    {
        /** @var self $dataSet */
        $dataSet = $validationContext->getDataSet();
        $result = new Result();
        $templates = $dataSet->getTemplates();
        if ($templates === []) {
            return $result;
        }
        if (!isset($templates[$value])) {
            $result->addError('Invalid template selection.');
        } else {
            $templatePath = $templates[$value];
            foreach ($dataSet->requiredTemplates() as $template) {
                if (!is_file($dataSet->aliases->get($templatePath . '/' . $template))) {
                    $result->addError("Unable to find the required code template file '$template'.");
                }
            }
        }

        return $result;
    }
}
