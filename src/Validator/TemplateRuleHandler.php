<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Validator;

use Yiisoft\Aliases\Aliases;
use Yiisoft\Validator\Exception\UnexpectedRuleException;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\RuleHandlerInterface;
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Yii\Gii\GiiParametersProvider;

final class TemplateRuleHandler implements RuleHandlerInterface
{
    public function __construct(
        private Aliases $aliases,
        private GiiParametersProvider $parametersProvider,
    ) {
    }

    /**
     * Validates the template selection.
     * This method validates whether the user selects an existing template
     * and the template contains all required template files as specified in {@see requiredTemplates()}.
     *
     * @param string $value
     */
    public function validate(mixed $value, object $rule, ValidationContext $context): Result
    {
        if (!$rule instanceof TemplateRule) {
            throw new UnexpectedRuleException(TemplateRule::class, $rule);
        }

        $result = new Result();
        $templates = $this->parametersProvider->getTemplates();
        if ($templates === []) {
            return $result;
        }
        if (!isset($templates[$value])) {
            $result->addError('Invalid template selection.');
        } else {
            $templatePath = $templates[$value];
            foreach ($this->parametersProvider->getRequiredTemplates() as $template) {
                if (!is_file($this->aliases->get($templatePath . '/' . $template))) {
                    $result->addError("Unable to find the required code template file '$template'.");
                }
            }
        }

        return $result;
    }
}
