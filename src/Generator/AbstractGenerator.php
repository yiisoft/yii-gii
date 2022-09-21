<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Generator;

use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Throwable;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Json\Json;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\View\Exception\ViewNotFoundException;
use Yiisoft\View\View;
use Yiisoft\View\ViewContextInterface;
use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\Exception\InvalidConfigException;
use Yiisoft\Yii\Gii\GeneratorInterface;

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
abstract class AbstractGenerator implements GeneratorInterface, DataSetInterface, ViewContextInterface
{
    private array $errors = [];
    /**
     * @var array a list of available code templates. The array keys are the template names,
     * and the array values are the corresponding template paths or path aliases.
     */
    private array $templates = [];
    /**
     * @var string the name of the code template that the user has selected.
     * The value of this property is internally managed by this class.
     */
    private string $template = 'default';
    private ?string $directory = null;

    public function __construct(
        protected Aliases $aliases,
        protected View $view,
        protected ValidatorInterface $validator,
    ) {
    }

    public function attributeLabels(): array
    {
        return [
            'enableI18N' => 'Enable I18N',
            'messageCategory' => 'Message Category',
        ];
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
     * Returns the list of sticky attributes.
     * A sticky attribute will remember its value and will initialize the attribute with this value
     * when the generator is restarted.
     *
     * @return array list of sticky attributes
     */
    public function stickyAttributes(): array
    {
        return ['template', 'enableI18N', 'messageCategory'];
    }

    /**
     * Returns the list of hint messages.
     * The array keys are the attribute names, and the array values are the corresponding hint messages.
     * Hint messages will be displayed to end users when they are filling the form for the generator.
     *
     * @return array the list of hint messages
     */
    public function hints(): array
    {
        return [
            'enableI18N' => 'This indicates whether the generator should generate strings using <code>Yii::t()</code> method.
                Set this to <code>true</code> if you are planning to make your application translatable.',
            'messageCategory' => 'This is the category used by <code>Yii::t()</code> in case you enable I18N.',
        ];
    }

    /**
     * Returns the list of auto complete values.
     * The array keys are the attribute names, and the array values are the corresponding auto complete values.
     * Auto complete values can also be callable typed in order one want to make postponed data generation.
     *
     * @return array the list of auto complete values
     */
    public function autoCompleteData(): array
    {
        return [];
    }

    /**
     * Returns the message to be displayed when the newly generated code is saved successfully.
     * Child classes may override this method to customize the message.
     *
     * @return string the message to be displayed when the newly generated code is saved successfully.
     */
    public function successMessage(): string
    {
        return 'The code has been generated successfully.';
    }

    /**
     * Returns the view file for the input form of the generator.
     * The default implementation will return the "form.php" file under the directory
     * that contains the generator class file.
     *
     * @throws ReflectionException
     *
     * @return string the view file for the input form of the generator.
     */
    public function formView(): string
    {
        $class = new ReflectionClass($this);

        return dirname($class->getFileName()) . '/form.php';
    }

    /**
     * Returns the root path to the default code template files.
     * The default implementation will return the "templates" subdirectory of the
     * directory containing the generator class file.
     *
     * @throws ReflectionException
     *
     * @return string the root path to the default code template files.
     */
    private function defaultTemplate(): string
    {
        $class = new ReflectionClass($this);

        return dirname($class->getFileName()) . '/default';
    }

    public function getDescription(): string
    {
        return '';
    }

    final public function validate(): Result
    {
        $result = $this->validator->validate($this, $this->rules());

        $this->errors = $result->getErrorMessagesIndexedByAttribute();
        return $result;
    }

    /**
     * Child classes should override this method like the following so that the parent
     * rules are included:
     *
     * ~~~
     * return array_merge(parent::rules(), [
     *     ...rules for the child class...
     * ]);
     * ~~~
     */
    public function rules(): array
    {
        return [
            'template' => [
                new Required(message: 'A code template must be selected.'),
                new Callback([$this, 'validateTemplate']),
            ],
        ];
    }

    /**
     * Loads sticky attributes from an internal file and populates them into the generator.
     *
     * @internal
     */
    public function loadStickyAttributes(): void
    {
        $stickyAttributes = $this->stickyAttributes();
        $path = $this->getStickyDataFile();
        if (is_file($path)) {
            $result = Json::decode(file_get_contents($path), true);
            if (is_array($result)) {
                foreach ($stickyAttributes as $name) {
                    $method = 'set' . $name;
                    if (array_key_exists($name, $result) && method_exists($this, $method)) {
                        $this->$method($result[$name]);
                    }
                }
            }
        }
    }

    /**
     * Loads sticky attributes from an internal file and populates them into the generator.
     *
     * @param array $data
     */
    public function load(array $data): void
    {
        foreach ($data as $name => $value) {
            $method = 'set' . $name;
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    /**
     * Saves sticky attributes into an internal file.
     */
    public function saveStickyAttributes(): void
    {
        $stickyAttributes = $this->stickyAttributes();
        $stickyAttributes[] = 'template';
        $values = [];
        foreach ($stickyAttributes as $name) {
            $method = 'get' . $name;
            if (method_exists($this, $method)) {
                $values[$name] = $this->$method();
            }
        }
        $path = $this->getStickyDataFile();
        if (!mkdir($concurrentDirectory = dirname($path), 0755, true) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        file_put_contents($path, Json::encode($values));
    }

    protected function getStickyDataFile(): string
    {
        return sprintf('%s/gii/%s.json', $this->aliases->get('@runtime'), str_replace('\\', '-', static::class));
    }

    /**
     * Saves the generated code into files.
     *
     * @param CodeFile[] $files the code files to be saved
     * @param array $answers
     * @param string[] $results this parameter receives a value from this method indicating the log messages
     * generated while saving the code files.
     *
     * @throws ReflectionException
     * @throws InvalidConfigException
     *
     * @return bool whether files are successfully saved without any error.
     */
    public function save(array $files, array $answers, array &$results): bool
    {
        $results = ['Generating code using template "' . $this->getTemplatePath() . '"...'];
        $hasError = false;
        foreach ($files as $file) {
            $relativePath = $file->getRelativePath();
            if (!empty($answers[$file->getId()]) && $file->getOperation() !== CodeFile::OP_SKIP) {
                try {
                    $file->save();
                    $results[] = $file->getOperation() === CodeFile::OP_CREATE
                        ? " generated  $relativePath"
                        : " overwrote  $relativePath";
                } catch (Exception $e) {
                    $hasError = true;
                    $results[] = sprintf(
                        "   generating %s\n    - <span class=\"error\">%s</span>",
                        $relativePath,
                        $e->getMessage()
                    );
                }
            } else {
                $results[] = "   skipped    $relativePath";
            }
        }
        $results[] = 'done!';

        return !$hasError;
    }

    public function getViewPath(): string
    {
        return $this->aliases->get($this->getTemplatePath());
    }

    /**
     * @throws ReflectionException
     * @throws InvalidConfigException
     *
     * @return string the root path of the template files that are currently being used.
     */
    public function getTemplatePath(): string
    {
        if ($this->template === 'default') {
            return $this->defaultTemplate();
        }

        if (isset($this->templates[$this->template])) {
            return $this->templates[$this->template];
        }

        throw new InvalidConfigException("Unknown template: {$this->template}");
    }

    /**
     * Generates code using the specified code template and parameters.
     * Note that the code template will be used as a PHP file.
     *
     * @param string $template the code template file. This must be specified as a file path
     * relative to {@see getTemplatePath()}.
     * @param array $params list of parameters to be passed to the template file.
     *
     * @throws Throwable
     * @throws ViewNotFoundException
     *
     * @return string the generated code
     */
    public function render(string $template, array $params = []): string
    {
        $params['generator'] = $this;

        return $this->view->withContext($this)->render($template, $params);
    }

    /**
     * Validates the template selection.
     * This method validates whether the user selects an existing template
     * and the template contains all required template files as specified in {@see requiredTemplates()}.
     *
     * @param string $value
     * @param ValidationContext $validationContext
     *
     * @return Result
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

    /**
     * An inline validator that checks if the attribute value refers to an existing class name.
     *
     * @param string $value the attribute being validated
     *
     * @return Result
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
     * An inline validator that checks if the attribute value refers to a valid namespaced class name.
     * The validator will check if the directory containing the new class file exist or not.
     *
     * @param mixed $value being validated
     * @param ValidationContext $validationContext
     *
     * @return Result
     */
    public function validateNewClass(mixed $value, Callback $rule, ValidationContext $validationContext): Result
    {
        /** @var self $dataSet */
        $dataSet = $validationContext->getDataSet();
        $result = new Result();
        $class = ltrim($value, '\\');
        if (($pos = strrpos($class, '\\')) !== false) {
            $ns = substr($class, 0, $pos);
            try {
                $path = $dataSet->aliases->get('@' . str_replace('\\', '/', $ns));
                if (!is_dir($path)) {
                    $result->addError("Please make sure the directory containing this class exists: $path");
                }
            } catch (InvalidArgumentException $exception) {
                $result->addError("The class namespace is invalid: $ns");
            }
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

    /**
     * Generates a string depending on enableI18N property
     *
     * @param string $string the text be generated
     * @param array $placeholders the placeholders to use by `Yii::t()`
     *
     * @return string
     */
    public function generateString(string $string = '', array $placeholders = []): string
    {
        $string = addslashes($string);
        if (!empty($placeholders)) {
            $phKeys = array_map(
                fn ($word) => '{' . $word . '}',
                array_keys($placeholders)
            );
            $phValues = array_values($placeholders);
            $str = "'" . str_replace($phKeys, $phValues, $string) . "'";
        } else {
            // No placeholders, just the given string
            $str = "'" . $string . "'";
        }
        return $str;
    }

    /**
     * @param string $attribute
     *
     * @return mixed
     */
    public function getAttributeValue(string $attribute): mixed
    {
        if (!$this->hasAttribute($attribute)) {
            throw new InvalidArgumentException(sprintf('There is no "%s" in %s.', $attribute, $this->getName()));
        }
        $method = 'get' . $attribute;
        return $this->$method();
    }

    public function hasAttribute(string $attribute): bool
    {
        $method = 'get' . $attribute;
        return method_exists($this, $method);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }

    public function setTemplates(array $templates): void
    {
        $this->templates = $templates;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    public function setDirectory(string $directory): void
    {
        $this->directory = $directory;
    }

    public function getData(): mixed
    {
        return [
            'templates' => $this->templates,
            'template' => $this->template,
        ];
    }
}
