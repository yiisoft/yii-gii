<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Validator;

use RuntimeException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Validator\DataSet\ObjectDataSet;
use Yiisoft\Validator\Exception\UnexpectedRuleException;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\RuleHandlerInterface;
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Yii\Gii\GeneratorCommandInterface;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\GiiInterface;
use Yiisoft\Yii\Gii\ParametersProvider;

final class TemplateRuleHandler implements RuleHandlerInterface
{
    public function __construct(
        private Aliases $aliases,
        private GiiInterface $gii,
        private ParametersProvider $parametersProvider,
    ) {
    }

    /**
     * Validates the template selection.
     * This method validates whether the user selects an existing template
     * and the template contains all required template files as specified in {@see requiredTemplates()}.
     */
    public function validate(mixed $value, object $rule, ValidationContext $context): Result
    {
        if (!$rule instanceof TemplateRule) {
            throw new UnexpectedRuleException(TemplateRule::class, $rule);
        }
        $result = new Result();

        if ($value === 'default') {
            return $result;
        }
        $command = $context->getRawData();
        if (!$command instanceof GeneratorCommandInterface) {
            throw new RuntimeException(sprintf(
                'Unsupported dataset class "%s".',
                get_debug_type($command)
            ));
        }
        $generator = $this->getGenerator($command);
        $templates = $this->parametersProvider->getTemplates($generator::getId());

        if ($templates === []) {
            return $result;
        }
        if (!isset($templates[$value])) {
            $result->addError(
                message: 'Template "{template}" does not exist. Known templates: {templates}',
                parameters: [
                    'template' => $value,
                    'templates' => implode(', ', array_keys($templates)),
                ],
            );
            return $result;
        }

        $templatePath = $templates[$value];
        foreach ($generator->getRequiredTemplates() as $template) {
            if (!is_file($this->aliases->get($templatePath . '/' . $template))) {
                $result->addError(
                    message: 'Unable to find the required code template file "{template}".',
                    parameters: ['template' => $template],
                );
            }
        }

        return $result;
    }

    private function getGenerator(GeneratorCommandInterface $dataSet): GeneratorInterface
    {
        foreach ($this->gii->getGenerators() as $generator) {
            if ($generator::getCommandClass() === $dataSet::class) {
                return $generator;
            }
        }
        throw new RuntimeException('Unknown generator');
    }
}
