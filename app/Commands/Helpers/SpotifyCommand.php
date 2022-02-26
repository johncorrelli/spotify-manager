<?php

declare(strict_types=1);

namespace App\Commands\Helpers;

use App\Models\Spotify\Spotify;

class SpotifyCommand
{
    protected string $signature;

    protected Spotify $spotify;

    /**
     * @var callable
     */
    protected $callback;

    public function __construct(string $signature, Spotify $spotify, callable $callback)
    {
        $this->signature = $signature;
        $this->spotify = $spotify;
        $this->callback = $callback;
    }

    public function onExecute(): void
    {
        if (\is_callable($this->callback)) {
            ($this->callback)();
        }
    }

    public function getSignature(): string
    {
        return $this->signature;
    }
}
