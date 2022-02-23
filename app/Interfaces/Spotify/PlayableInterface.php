<?php

namespace App\Interfaces\Spotify;

interface PlayableInterface
{
    public function getDuration(): int;

    public function getName(): string;

    public function getId(): string;

    public function getComment(): string;
}
