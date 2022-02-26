<?php

declare(strict_types=1);

namespace App\Commands\Helpers;

use App\Models\Spotify\Spotify;

/**
 * @property Spotify $spotify
 */
trait ManageHelpers
{
    /**
     * @return SpotifyCommand[]
     */
    protected function getCommands(): array
    {
        return [
            new SpotifyCommand(
                'Block Current Track',
                $this->spotify,
                fn () => $this->spotify->blockTrack($this->spotify->getSkippables())
                    && $this->spotify->nextTrack()
            ),

            new SpotifyCommand(
                'Block Current Album',
                $this->spotify,
                fn () => $this->spotify->blockAlbum($this->spotify->getSkippables())
                    && $this->spotify->nextTrack()
            ),

            new SpotifyCommand(
                'Block Current Artist',
                $this->spotify,
                fn () => $this->spotify->blockArtist($this->spotify->getSkippables())
                    && $this->spotify->nextTrack()
            ),

            new SpotifyCommand('Play Next Track', $this->spotify, fn () => $this->spotify->nextTrack()),
        ];
    }
}
