<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator;

use Exception;
use ReflectionClass;
use ReflectionException;
use Throwable;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\Exception\InvalidConfigException;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\GiiParametersProvider;

/**
 * This is the base class for all generator classes.
 *
 * A generator instance is responsible for taking user inputs, validating them,
 * and using them to generate the corresponding code based on a set of code template files.
 *
 * A generator class typically needs to implement the following methods:
 *
 * - {@see GeneratorInterface::getName()}: returns the name of the generator
 * - {@see GeneratorInterface::getDescription()}: returns the detailed description of the generator
 * - {@see GeneratorInterface::validate()}: returns generator validation result
 * - {@see GeneratorInterface::generate()}: generates the code based on the current user input and the specified code
 * template files. This is the place where main code generation code resides.
 */
abstract class AbstractGenerator implements GeneratorInterface
{
    private string $directory = 'src/Controller';

    public function __construct(
        protected Aliases $aliases,
        protected ValidatorInterface $validator,
        protected GiiParametersProvider $parametersProvider,
    ) {
    }

    /**
     * Returns a list of code template files that are required.
     * Derived classes usually should override this method if they require the existence of
     * certain template files.
     *
     * @return array list of code template files that are required. They should be file paths
     * relative to {@see getTemplatePath()}.
     */
    public function requiredTemplates(): array
    {
        return [];
    }

    /**
     * Returns the root path to the default code template files.
     * The default implementation will return the "templates" subdirectory of the
     * directory containing the generator class file.
     *
     * @return string the root path to the default code template files.
     * @throws ReflectionException
     *
     */
    private function defaultTemplate(): string
    {
        $class = new ReflectionClass($this);

        return dirname($class->getFileName()) . '/default';
    }

    public function validate(AbstractGeneratorCommand $command): Result
    {
        return $this->validator->validate($command);
    }

    /**
     * @return string the root path of the template files that are currently being used.
     * @throws InvalidConfigException
     *
     * @throws ReflectionException
     */
    public function getTemplatePath(AbstractGeneratorCommand $command): string
    {
        $template = $command->getTemplate();

        if ($template === 'default') {
            return $this->defaultTemplate();
        }

        if (isset($this->parametersProvider->getTemplates()[$template])) {
            return $this->parametersProvider->getTemplates()[$template];
        }

        throw new InvalidConfigException("Unknown template: {$template}");
    }

    /**
     * Generates code using the specified code template and parameters.
     * Note that the code template will be used as a PHP file.
     *
     * @param string $template the code template file. This must be specified as a file path
     * relative to {@see getTemplatePath()}.
     * @param array $params list of parameters to be passed to the template file.
     *
     * @return string the generated code
     * @throws Throwable
     *
     */
    protected function render(AbstractGeneratorCommand $command, string $template, array $params = []): string
    {
        $file = sprintf(
            '%s/%s.php',
            $this->aliases->get($this->getTemplatePath($command)),
            $template
        );

        $renderer = function (): void {
            extract(func_get_arg(1));
            /** @psalm-suppress UnresolvableInclude */
            require func_get_arg(0);
        };

        $obInitialLevel = ob_get_level();
        ob_start();
        ob_implicit_flush(false);
        try {
            /** @psalm-suppress PossiblyInvalidFunctionCall */
            $renderer->bindTo($this)($file, array_merge($params, ['command' => $command]));
            return ob_get_clean();
        } catch (Throwable $e) {
            while (ob_get_level() > $obInitialLevel) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        }
    }

    /**
     * An inline validator that checks if the attribute value refers to an existing class name.
     *
     * @param string $value the attribute being validated
     */
    public function validateClass(string $value): Result
    {
        $result = new Result();
        if (!class_exists($value)) {
            $result->addError("Class '$value' does not exist or has syntax error.");
        }

        return $result;
    }

    /**
     * @param string $value the attribute to be validated
     *
     * @return bool whether the value is a reserved PHP keyword.
     */
    public function isReservedKeyword(string $value): bool
    {
        static $keywords = [
            '__class__',
            '__dir__',
            '__file__',
            '__function__',
            '__line__',
            '__method__',
            '__namespace__',
            '__trait__',
            'abstract',
            'and',
            'array',
            'as',
            'break',
            'case',
            'catch',
            'callable',
            'cfunction',
            'class',
            'clone',
            'const',
            'continue',
            'declare',
            'default',
            'die',
            'do',
            'echo',
            'else',
            'elseif',
            'empty',
            'enddeclare',
            'endfor',
            'endforeach',
            'endif',
            'endswitch',
            'endwhile',
            'eval',
            'exception',
            'exit',
            'extends',
            'final',
            'finally',
            'for',
            'foreach',
            'function',
            'global',
            'goto',
            'if',
            'implements',
            'include',
            'include_once',
            'instanceof',
            'insteadof',
            'interface',
            'isset',
            'list',
            'namespace',
            'new',
            'old_function',
            'or',
            'parent',
            'php_user_filter',
            'print',
            'private',
            'protected',
            'public',
            'require',
            'require_once',
            'return',
            'static',
            'switch',
            'this',
            'throw',
            'trait',
            'try',
            'unset',
            'use',
            'var',
            'while',
            'xor',
            'fn',
        ];

        return in_array(strtolower($value), $keywords, true);
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }
}
