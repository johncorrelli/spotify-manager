<?php

namespace App\Models\Storage;

class Skippables extends AccessesDisk
{
    public function get(): object
    {
        return $this->getFileContents();
    }

    public function write(object $contents): void
    {
        $this->contents = $contents;

        $this->save();
    }
}
