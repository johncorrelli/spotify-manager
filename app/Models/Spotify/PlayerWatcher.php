<?php

namespace App\Models\Spotify;

use App\Exceptions\SpotifyException;
use App\Interfaces\Spotify\PlayableInterface;
use App\Interfaces\Spotify\PlayerInterface;
use App\Models\Spotify\Playable\Artist;
use App\Models\Spotify\Playable\Track;
use App\Models\Spotify\Responses\Player;
use App\Models\Storage\Skippables;

/**
 * @property Api $api
 */
trait PlayerWatcher
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

    public function watchPlayer(): void
    {
        $player = $this->getPlayingTrackOrDie();

        if ($this->isSkippable($player->getItem())) {
            $this->nextTrack();

            echo "Skipping song... \n";
            sleep(1);

            $this->watchPlayer(); // @phan-suppress-current-line PhanPossiblyInfiniteRecursionSameParams

            return;
        }

        $this->waitAndReRun($player);
    }

    public function listCurrent(): string
    {
        $player = $this->getPlayingTrackOrDie();

        return $player->getItem()->getComment();
    }

    protected function waitAndReRun(PlayerInterface $currentPlaying): void
    {
        $progress = $currentPlaying->getProgress();
        $duration = $currentPlaying->getItem()->getDuration();

        $waitBuffer = 2000;
        $reRunInMicroseconds = ($duration - $progress + $waitBuffer) * 1000;

        echo "Waiting for current song to finish playing before rerunning...\n";
        usleep($reRunInMicroseconds);

        $this->watchPlayer();
    }

    protected function getPlayingTrackOrDie(): PlayerInterface
    {
        $currentlyPlaying = Player::fromResponse($this->api->request('GET', 'me/player') ?? (object) []);

        if (!$currentlyPlaying->isPlaying()) {
            echo "Spotify is not playing. Exiting...";
            exit;
        }

        if ($currentlyPlaying->getType() !== Player::TRACK) {
            echo "Not listening to a track. Exiting...";
            exit;
        }

        return $currentlyPlaying;
    }

    protected function isSkippable(PlayableInterface $track): bool
    {
        if (!isset($this->skippables)) {
            throw new SpotifyException("Skippables not set!");
        }
        if (!($track instanceof Track)) {
            throw new SpotifyException("A Track is not playing");
        }

        $itemsToSkip = $this->getSkippables()->get();
        $skippableSongIds = array_column($itemsToSkip->songs, 'songId');
        $skippableAlbumIds = array_column($itemsToSkip->albums, 'albumId');
        $skippableArtistIds = array_column($itemsToSkip->artists, 'artistId');

        $shouldSkipSong = in_array($track->getId(), $skippableSongIds);
        $shouldSkipAlbum = in_array($track->getAlbum()->getId(), $skippableAlbumIds);

        $currentArtistIds = array_map(fn (Artist $artist) => $artist->getId(), $track->getArtists());
        $shouldSkipArtist = !empty(array_intersect($skippableArtistIds, $currentArtistIds));

        return $shouldSkipSong || $shouldSkipArtist || $shouldSkipAlbum;
    }

    protected function nextTrack(): void
    {
        $this->api->request('POST', 'me/player/next');
    }
}
