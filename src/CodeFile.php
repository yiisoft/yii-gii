use RuntimeException;
     * @return bool the error occurred while saving the code file, or true if no error.
    public function save(): bool
                    throw new RuntimeException("Unable to create the directory '$dir'.");
            throw new RuntimeException("Unable to write the file '{$this->path}'.");