<?php

declare(strict_types=1);

namespace App\Models\Storage;

class AccessesDisk
{
    protected string $filePath;

    protected object $contents;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->contents = (object) [];
    }

    /**
     * Returns all saved contents.
     */
    protected function getFileContents(): object
    {
        if (!file_exists($this->filePath)) {
            return json_decode('{}');
        }

        return json_decode(
            file_get_contents($this->filePath),
        );
    }

    /**
     * Updates the storage file with all current values.
     */
    protected function save(): void
    {
        $directory = \dirname($this->filePath);

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($this->filePath, json_encode($this->contents, JSON_PRETTY_PRINT));
    }
}
