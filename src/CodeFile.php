<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii;

use Diff;
use RuntimeException;
use Yiisoft\Html\Html;
use Yiisoft\Yii\Gii\Component\DiffRendererHtmlInline;

/**
 * CodeFile represents a code file to be generated.
 */
final class CodeFile
{
    /**
     * The new file mode
     */
    private const FILE_MODE = 0666;
    /**
     * The new directory mode
     */
    private const DIR_MODE = 0777;
    /**
     * The code file is new.
     */
    public const OP_CREATE = 0;
    /**
     * The code file already exists, and the new one may need to overwrite it.
     */
    public const OP_OVERWRITE = 1;
    /**
     * The new code file and the existing one are identical.
     */
    public const OP_SKIP = 2;
    /**
     * @var string an ID that uniquely identifies this code file.
     */
    private string $id;
    /**
     * @var string the file path that the new code should be saved to.
     */
    private string $path;
    /**
     * @var string the newly generated code content
     */
    private string $content;
    /**
     * @var int the operation to be performed. This can be [[OP_CREATE]], [[OP_OVERWRITE]] or [[OP_SKIP]].
     */
    private int $operation;
    /**
     * @var string the base path
     */
    private string $basePath = '';
    /**
     * @var int the permission to be set for newly generated code files.
     * This value will be used by PHP chmod function.
     * Defaults to 0666, meaning the file is read-writable by all users.
     */
    private int $newFileMode = self::FILE_MODE;
    /**
     * @var int the permission to be set for newly generated directories.
     * This value will be used by PHP chmod function.
     * Defaults to 0777, meaning the directory can be read, written and executed by all users.
     */
    private int $newDirMode = self::DIR_MODE;

    /**
     * Constructor.
     *
     * @param string $path the file path that the new code should be saved to.
     * @param string $content the newly generated code content.
     */
    public function __construct(string $path, string $content)
    {
        $this->path = strtr($path, '/\\', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR);
        $this->content = $content;
        $this->id = dechex(crc32($this->path));
        $this->operation = self::OP_CREATE;
        if (is_file($path)) {
            $this->operation = file_get_contents($path) === $content ? self::OP_SKIP : self::OP_OVERWRITE;
        }
    }

    /**
     * Saves the code into the file specified by [[path]].
     *
     * @return bool the error occurred while saving the code file, or true if no error.
     */
    public function save(): bool
    {
        if ($this->operation === self::OP_CREATE) {
            $dir = dirname($this->path);
            if (!is_dir($dir)) {
                if ($this->newDirMode !== self::DIR_MODE) {
                    $mask = @umask(0);
                    $result = @mkdir($dir, $this->newDirMode, true);
                    @umask($mask);
                } else {
                    $result = @mkdir($dir, 0777, true);
                }
                if (!$result) {
                    throw new RuntimeException("Unable to create the directory '$dir'.");
                }
            }
        }
        if (@file_put_contents($this->path, $this->content) === false) {
            throw new RuntimeException("Unable to write the file '{$this->path}'.");
        }

        if ($this->newFileMode !== self::FILE_MODE) {
            $mask = @umask(0);
            @chmod($this->path, $this->newFileMode);
            @umask($mask);
        }

        return true;
    }

    /**
     * @return string the code file path relative to the application base path.
     */
    public function getRelativePath(): string
    {
        if (!empty($this->basePath) && strpos($this->path, $this->basePath) === 0) {
            return substr($this->path, strlen($this->basePath) + 1);
        }

        return $this->path;
    }

    /**
     * @return string the code file extension (e.g. php, txt)
     */
    public function getType(): string
    {
        if (($pos = strrpos($this->path, '.')) !== false) {
            return substr($this->path, $pos + 1);
        }

        return 'unknown';
    }

    /**
     * Returns preview or false if it cannot be rendered
     *
     * @return bool|string
     */
    public function preview()
    {
        if (($pos = strrpos($this->path, '.')) !== false) {
            $type = substr($this->path, $pos + 1);
        } else {
            $type = 'unknown';
        }

        if ($type === 'php') {
            return highlight_string($this->content, true);
        }

        if (!in_array($type, ['jpg', 'gif', 'png', 'exe'])) {
            return nl2br(Html::encode($this->content));
        }

        return false;
    }

    /**
     * Returns diff or false if it cannot be calculated
     *
     * @return bool|string
     */
    public function diff()
    {
        $type = strtolower($this->getType());
        if (in_array($type, ['jpg', 'gif', 'png', 'exe'])) {
            return false;
        }

        if ($this->operation === self::OP_OVERWRITE) {
            return $this->renderDiff(file($this->path), $this->content);
        }

        return '';
    }

    /**
     * Renders diff between two sets of lines
     *
     * @param mixed $lines1
     * @param mixed $lines2
     *
     * @return string
     */
    private function renderDiff($lines1, $lines2): string
    {
        if (!is_array($lines1)) {
            $lines1 = explode("\n", $lines1);
        }
        if (!is_array($lines2)) {
            $lines2 = explode("\n", $lines2);
        }
        foreach ($lines1 as $i => $line) {
            $lines1[$i] = rtrim($line, "\r\n");
        }
        foreach ($lines2 as $i => $line) {
            $lines2[$i] = rtrim($line, "\r\n");
        }

        $renderer = new DiffRendererHtmlInline();
        $diff = new Diff($lines1, $lines2);

        return $diff->render($renderer);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOperation(): int
    {
        return $this->operation;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function withBasePath(string $basePath): self
    {
        $new = clone $this;
        $new->basePath = $basePath;

        return $new;
    }

    public function withNewFileMode(int $mode): self
    {
        $new = clone $this;
        $new->newFileMode = $mode;

        return $new;
    }

    public function withNewDirMode(int $mode): self
    {
        $new = clone $this;
        $new->newDirMode = $mode;

        return $new;
    }
}
