<?php

namespace App\Models\Spotify\Responses;

use App\Exceptions\Spotify\SpotifyException;
use App\Interfaces\Spotify\PlayableInterface;
use App\Interfaces\Spotify\PlayerInterface;
use App\Models\Spotify\Playable\Album;
use App\Models\Spotify\Playable\Artist;
use App\Models\Spotify\Playable\Track;

class Player implements PlayerInterface
{
    public const TRACK = 'track';

    private string $type;

    private PlayableInterface $item;

    private bool $isPlaying;

    private int $progress;

    public function __construct(string $type, PlayableInterface $item)
    {
        $this->type = $type;
        $this->item = $item;
    }

    public function getItem(): PlayableInterface
    {
        return $this->item;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setIsPlaying(bool $isPlaying): self
    {
        $this->isPlaying = $isPlaying;

        return $this;
    }

    public function getProgress(): ?int
    {
        return $this->progress;
    }

    public function setProgress(int $progress): self
    {
        $this->progress = $progress;

        return $this;
    }

    public function isPlaying(): bool
    {
        return $this->isPlaying;
    }

    public static function fromResponse(object $response): PlayerInterface
    {
        $item = $response->item ?? null;
        $type = $item->type ?? null;

        if ($type === 'track') {
            return self::fromTrackResponse($response);
        }

        throw new SpotifyException('Invalid playing object');
    }

    private static function fromTrackResponse(object $response): PlayerInterface
    {
        $type = self::TRACK;
        $item = $response->item ?? null;

        if (!$item) {
            throw new SpotifyException("Spotify response does not contain an item");
        }

        $album = new Album($item->album->id, $item->album->name);
        $artists = array_map(
            fn ($artist) => new Artist($artist->id, $artist->name),
            $item->artists
        );

        $track = new Track($item->id, $item->name, $album, $artists);
        $track->setDuration($item->duration_ms);

        $player = new Player($type, $track);
        $player->setIsPlaying($response->is_playing);
        $player->setProgress($response->progress_ms);

        return $player;
    }
}
