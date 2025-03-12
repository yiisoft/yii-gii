<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Gii\Component\CodeFile;

use Diff;
use Diff_Renderer_Text_Unified;
use RuntimeException;

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
     * @var string an ID that uniquely identifies this code file.
     */
    private string $id;
    /**
     * @var string the file path that the new code should be saved to.
     */
    private string $path;
    /**
     * @var CodeFileWriteOperationEnum the operation to be performed. This can be {@see OP_CREATE}, {@see OP_OVERWRITE}
     *     or {@see OP_SKIP}.
     */
    private CodeFileWriteOperationEnum $operation = CodeFileWriteOperationEnum::SAVE;
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
    private CodeFileStateEnum $state = CodeFileStateEnum::NOT_EXIST;

    /**
     * Constructor.
     *
     * @param string $path the file path that the new code should be saved to.
     * @param string $content the newly generated code content.
     */
    public function __construct(string $path, private string $content)
    {
        $this->path = $this->preparePath($path);
        $this->id = dechex(crc32($this->path));
        if (is_file($path)) {
            if (file_get_contents($path) === $content) {
                $this->operation = CodeFileWriteOperationEnum::SKIP;
                $this->state = CodeFileStateEnum::PRESENT_SAME;
            } else {
                $this->operation = CodeFileWriteOperationEnum::SAVE;
                $this->state = CodeFileStateEnum::PRESENT_DIFFERENT;
            }
        }
    }

    /**
     * Saves the code into the file specified by [[path]].
     *
     * @return CodeFileWriteStatusEnum the error occurred while saving the code file, or true if no error.
     */
    public function save(): CodeFileWriteStatusEnum
    {
        if ($this->operation === CodeFileWriteOperationEnum::SAVE && $this->state !== CodeFileStateEnum::PRESENT_SAME) {
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
        $status = match ($this->state) {
            CodeFileStateEnum::PRESENT_DIFFERENT => CodeFileWriteStatusEnum::OVERWROTE,
            CodeFileStateEnum::NOT_EXIST => CodeFileWriteStatusEnum::CREATED,
            default => CodeFileWriteStatusEnum::SKIPPED,
        };
        if (@file_put_contents($this->path, $this->content) === false) {
            throw new RuntimeException("Unable to write the file '{$this->path}'.");
        }

        if ($this->newFileMode !== self::FILE_MODE) {
            $mask = @umask(0);
            @chmod($this->path, $this->newFileMode);
            @umask($mask);
        }

        return $status;
    }

    /**
     * @return string the code file path relative to the application base path.
     */
    public function getRelativePath(): string
    {
        if (!empty($this->basePath) && str_starts_with($this->path, $this->basePath)) {
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
     */
    public function preview(): false|string
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
            $content = htmlspecialchars(
                $this->content,
                ENT_NOQUOTES | ENT_SUBSTITUTE | ENT_HTML5,
                'UTF-8'
            );
            return nl2br($content);
        }

        return false;
    }

    /**
     * Returns diff or false if it cannot be calculated
     */
    public function diff(): false|string
    {
        $type = strtolower($this->getType());
        if (in_array($type, ['jpg', 'gif', 'png', 'exe'])) {
            return false;
        }

        if ($this->state === CodeFileStateEnum::PRESENT_DIFFERENT) {
            return $this->renderDiff(file($this->path), $this->content);
        }

        return '';
    }

    /**
     * Renders diff between two sets of lines
     */
    private function renderDiff(mixed $lines1, mixed $lines2): string
    {
        if (!is_array($lines1)) {
            $lines1 = explode("\n", (string)$lines1);
        }
        if (!is_array($lines2)) {
            $lines2 = explode("\n", (string)$lines2);
        }

        /**
         * @var string $line
         */
        foreach ($lines1 as $i => $line) {
            $lines1[$i] = rtrim($line, "\r\n");
        }

        /**
         * @var string $line
         */
        foreach ($lines2 as $i => $line) {
            $lines2[$i] = rtrim($line, "\r\n");
        }

        $renderer = new Diff_Renderer_Text_Unified();
        $diff = new Diff($lines1, $lines2);

        return (string)$diff->render($renderer);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOperation(): CodeFileWriteOperationEnum
    {
        return $this->operation;
    }

    public function getState(): CodeFileStateEnum
    {
        return $this->state;
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
        $new->basePath = $this->preparePath($basePath);

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

    private function preparePath(string $path): string
    {
        return strtr($path, '/\\', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR);
    }
}
