<?php

namespace Yiisoft\Yii\Gii\Generators;

use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Throwable;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Json\Json;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\ResultSet;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Validator;
use Yiisoft\VarDumper\VarDumper;
use Yiisoft\View\Exception\ViewNotFoundException;
use Yiisoft\Yii\Gii\CodeFile;
use Yiisoft\Yii\Gii\GeneratorInterface;
use Yiisoft\Yii\Gii\Parameters;
use Yiisoft\Yii\Web\Info;

/**
 * This is the base class for all generator classes.
 *
 * A generator instance is responsible for taking user inputs, validating them,
 * and using them to generate the corresponding code based on a set of code template files.
 *
 * A generator class typically needs to implement the following methods:
 *
 * - [[getName()]]: returns the name of the generator
 * - [[getDescription()]]: returns the detailed description of the generator
 * - [[generate()]]: generates the code based on the current user input and the specified code template files.
 *   This is the place where main code generation code resides.
 *
 */
abstract class Generator implements GeneratorInterface, DataSetInterface
{
    private array $errors = [];
    protected Aliases    $aliases;
    protected Parameters $parameters;
    /**
     * @var array a list of available code templates. The array keys are the template names,
     * and the array values are the corresponding template paths or path aliases.
     */
    public array $templates = [];
    /**
     * @var string the name of the code template that the user has selected.
     * The value of this property is internally managed by this class.
     */
    public string $template = 'default';
    /**
     * @var bool whether the strings will be generated using `Yii::t()` or normal strings.
     */
    public bool $enableI18N = false;
    /**
     * @var string the message category used by `Yii::t()` when `$enableI18N` is `true`.
     * Defaults to `app`.
     */
    public string $messageCategory = 'app';

    public function __construct(Aliases $aliases, Parameters $parameters)
    {
        $this->aliases = $aliases;
        $this->parameters = $parameters;
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
     * @return array list of code template files that are required. They should be file paths
     * relative to [[templatePath]].
     */
    public function requiredTemplates(): array
    {
        return [];
    }

    /**
     * Returns the list of sticky attributes.
     * A sticky attribute will remember its value and will initialize the attribute with this value
     * when the generator is restarted.
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
     * @return array the list of auto complete values
     */
    public function autoCompleteData(): array
    {
        return [];
    }

    /**
     * Returns the message to be displayed when the newly generated code is saved successfully.
     * Child classes may override this method to customize the message.
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
     * @return string the view file for the input form of the generator.
     * @throws ReflectionException
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
     * @return string the root path to the default code template files.
     * @throws ReflectionException
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

    /**
     * @return bool|ResultSet
     */
    final public function validate()
    {
        $results = (new Validator($this->rules()))->validate($this);
        foreach ($results as $attribute => $resultItem) {
            if (!$resultItem->isValid()) {
                $this->errors[$attribute] = $resultItem->getErrors();
            }
        }
        return !$this->hasErrors();
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
                (new Required())->message('A code template must be selected.')
            ],
        ];
    }

    /**
     * Loads sticky attributes from an internal file and populates them into the generator.
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
                    if (isset($result[$name]) && property_exists($this, $name)) {
                        $this->$name = $result[$name];
                    }
                }
            }
        }
    }

    /**
     * Loads sticky attributes from an internal file and populates them into the generator.
     * @param array $data
     */
    public function load(array $data): void
    {
        foreach ($data as $name => $value) {
            if ($this->hasAttribute($name)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * Saves sticky attributes into an internal file.
     * @internal
     */
    public function saveStickyAttributes(): void
    {
        $stickyAttributes = $this->stickyAttributes();
        $stickyAttributes[] = 'template';
        $values = [];
        foreach ($stickyAttributes as $name) {
            $values[$name] = $this->$name;
        }
        $path = $this->getStickyDataFile();
        if (!mkdir($concurrentDirectory = dirname($path), 0755, true) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        file_put_contents($path, json_encode($values));
    }

    /**
     * @return string the file path that stores the sticky attribute values.
     * @internal
     */
    public function getStickyDataFile()
    {
        return $this->aliases->get('@runtime') . '/gii-' . Info::frameworkVersion() . '/' . str_replace(
                '\\',
                '-',
                get_class($this)
            ) . '.json';
    }

    /**
     * Saves the generated code into files.
     * @param CodeFile[] $files the code files to be saved
     * @param array $answers
     * @param string $results this parameter receives a value from this method indicating the log messages
     * generated while saving the code files.
     * @return bool whether files are successfully saved without any error.
     * @throws ReflectionException
     */
    public function save($files, $answers, &$results): bool
    {
        $lines = ['Generating code using template "' . $this->getTemplatePath() . '"...'];
        $hasError = false;
        foreach ($files as $file) {
            $relativePath = $file->getRelativePath();
            if (isset($answers[$file->getId()]) && !empty($answers[$file->getId()]) && $file->getOperation(
                ) !== CodeFile::OP_SKIP) {
                $error = $file->save();
                if (is_string($error)) {
                    $hasError = true;
                    $lines[] = "generating $relativePath\n<span class=\"error\">$error</span>";
                } else {
                    $lines[] = $file->getOperation(
                    ) === CodeFile::OP_CREATE ? " generated $relativePath" : " overwrote $relativePath";
                }
            } else {
                $lines[] = "   skipped $relativePath";
            }
        }
        $lines[] = "done!\n";
        $results = implode("\n", $lines);

        return !$hasError;
    }

    /**
     * @return string the root path of the template files that are currently being used.
     * @throws ReflectionException
     */
    public function getTemplatePath(): string
    {
        if ($this->template === 'default') {
            return $this->defaultTemplate();
        }

        if (isset($this->templates[$this->template])) {
            return $this->templates[$this->template];
        }

        throw new RuntimeException("Unknown template: {$this->template}");
    }

    /**
     * Generates code using the specified code template and parameters.
     * Note that the code template will be used as a PHP file.
     * @param string $template the code template file. This must be specified as a file path
     * relative to [[templatePath]].
     * @param array $params list of parameters to be passed to the template file.
     * @return string the generated code
     * @throws ReflectionException
     * @throws Throwable
     * @throws ViewNotFoundException
     */
    public function render($template, $params = []): string
    {
        $params['generator'] = $this;

        return $this->renderTemplate($this->getTemplatePath() . '/' . $template, $params);
    }

    protected function renderTemplate(string $template, array $params): string
    {
        $renderer = function (...$args) {
            extract($args[1], EXTR_OVERWRITE);
            require $args[0];
        };

        $obInitialLevel = ob_get_level();
        ob_start();
        ob_implicit_flush(0);
        try {
            $renderer->bindTo($this)($template, $params);
            return ob_get_clean();
        } catch (\Throwable $e) {
            while (ob_get_level() > $obInitialLevel) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        }
    }

    /**
     * @param string $value the attribute to be validated
     * @return bool whether the value is a reserved PHP keyword.
     */
    public function isReservedKeyword($value): bool
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
        ];

        return in_array(strtolower($value), $keywords, true);
    }

    /**
     * Generates a string depending on enableI18N property
     *
     * @param string $string the text be generated
     * @param array $placeholders the placeholders to use by `Yii::t()`
     * @return string
     */
    public function generateString($string = '', $placeholders = [])
    {
        $string = addslashes($string);
        if ($this->enableI18N) {
            // If there are placeholders, use them
            if (!empty($placeholders)) {
                $ph = ', ' . VarDumper::export($placeholders);
            } else {
                $ph = '';
            }
            $str = "Yii::t('" . $this->messageCategory . "', '" . $string . "'" . $ph . ")";
        } elseif (!empty($placeholders)) {
            $phKeys = array_map(
                static function ($word) {
                    return '{' . $word . '}';
                },
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

    public function getAttributeValue(string $attribute)
    {
        if (!$this->hasAttribute($attribute)) {
            throw new \InvalidArgumentException(sprintf('There is no "%s" in %s.', $attribute, $this->getName()));
        }

        return $this->$attribute;
    }

    public function hasAttribute(string $attribute): bool
    {
        return isset($this->$attribute);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return $this->errors !== [];
    }
}
