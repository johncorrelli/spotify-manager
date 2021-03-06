<?php

declare(strict_types=1);

namespace App\Models\Spotify\Playable;

use App\Exceptions\Spotify\SpotifyException;
use App\Interfaces\Spotify\PlayableInterface;

class BasePlayable implements PlayableInterface
{
    protected string $id;

    protected string $name;

    public function __construct(string $id, string $name)
    {
        $this->id = $id;

        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDuration(): int
    {
        throw new SpotifyException('Undefined function');
    }

    public function getComment(): string
    {
        return $this->getName();
    }
}
