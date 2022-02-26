<?php

declare(strict_types=1);

namespace App\Interfaces\Spotify;

interface PlayerInterface
{
    public function getItem(): PlayableInterface;

    public function isPlaying(): bool;

    public function getProgress(): ?int;

    public function getType(): string;
}
