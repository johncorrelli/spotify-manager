<?php

declare(strict_types=1);

namespace App\Models\Storage;

class Skippables extends AccessesDisk
{
    public function get(): object
    {
        $db = $this->getFileContents();

        if (!property_exists($db, 'songs')) {
            $db->songs = [];
        }
        if (!property_exists($db, 'albums')) {
            $db->albums = [];
        }
        if (!property_exists($db, 'artists')) {
            $db->artists = [];
        }

        return $db;
    }

    public function write(object $contents): void
    {
        $this->contents = $contents;

        $this->save();
    }
}
