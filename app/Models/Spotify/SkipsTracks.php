<?php

declare(strict_types=1);

namespace App\Models\Spotify;

use App\Exceptions\Spotify\NotPlayingTrackException;
use App\Exceptions\Spotify\SpotifyException;
use App\Interfaces\Spotify\PlayableInterface;
use App\Models\Spotify\Playable\Artist;
use App\Models\Spotify\Playable\Track;
use App\Models\Storage\Skippables;

trait SkipsTracks
{
    protected Skippables $skippables;

    public function setSkippables(Skippables $skippables)
    {
        $this->skippables = $skippables;

        return $this;
    }

    public function getSkippables(): Skippables
    {
        return $this->skippables;
    }

    protected function isSkippable(PlayableInterface $track): bool
    {
        if (!isset($this->skippables)) {
            throw new SpotifyException('Skippables not set!');
        }
        if (!($track instanceof Track)) {
            throw new NotPlayingTrackException();
        }

        $itemsToSkip = $this->getSkippables()->get();
        $skippableSongIds = array_column($itemsToSkip->songs, 'songId');
        $skippableAlbumIds = array_column($itemsToSkip->albums, 'albumId');
        $skippableArtistIds = array_column($itemsToSkip->artists, 'artistId');

        $shouldSkipSong = \in_array($track->getId(), $skippableSongIds, true);
        $shouldSkipAlbum = \in_array($track->getAlbum()->getId(), $skippableAlbumIds, true);

        $currentArtistIds = array_map(fn (Artist $artist) => $artist->getId(), $track->getArtists());
        $shouldSkipArtist = !empty(array_intersect($skippableArtistIds, $currentArtistIds));

        return $shouldSkipSong || $shouldSkipArtist || $shouldSkipAlbum;
    }
}
