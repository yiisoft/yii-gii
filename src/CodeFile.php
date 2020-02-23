use Yiisoft\Html\Html;
final class CodeFile
    /**
     * The new file mode
     */
    private const FILE_MODE = 0666;
    /**
     * The new directory mode
     */
    private const DIR_MODE = 0777;
    public const OP_CREATE = 0;
    public const OP_OVERWRITE = 1;
    public const OP_SKIP = 2;
    private string $id;
    private string $path;
    private string $content;
     * @var int the operation to be performed. This can be [[OP_CREATE]], [[OP_OVERWRITE]] or [[OP_SKIP]].
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
    public function __construct(string $path, string $content)
        $this->id = sha1($this->path);
                if ($this->newDirMode !== self::DIR_MODE) {
                    $mask = @umask(0);
                    $result = @mkdir($dir, $this->newDirMode, true);
                    @umask($mask);
                } else {
                    $result = @mkdir($dir, 0777, true);
                }
        }

        if ($this->newFileMode !== self::FILE_MODE) {
            @chmod($this->path, $this->newFileMode);
    public function getRelativePath(): string
        if (!empty($this->basePath) && strpos($this->path, $this->basePath) === 0) {
            return substr($this->path, strlen($this->basePath) + 1);

        return $this->path;
    public function getType(): string

        return 'unknown';
        }

        if (!in_array($type, ['jpg', 'gif', 'png', 'exe'])) {

        return false;
        }

        if ($this->operation === self::OP_OVERWRITE) {

        return '';
    private function renderDiff($lines1, $lines2): string

    public function getId(): string
    {
        return $this->id;
    }

    public function getOperation(): string
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