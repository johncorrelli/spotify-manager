<?php

declare(strict_types=1);

namespace App\Models\Spotify;

use App\Exceptions\Spotify\NotPlayingException;
use App\Exceptions\Spotify\NotPlayingTrackException;
use App\Interfaces\Spotify\PlayerInterface;
use App\Models\Spotify\Responses\Player;

/**
 * @property Api $api
 */
trait ControlsPlayer
{
    public function listCurrent(): string
    {
        $player = $this->getPlayingTrackOrDie();

        return $player->getItem()->getComment();
    }

    public function nextTrack(): void
    {
        $this->api->request('POST', 'me/player/next');
    }

    public function setVolume(int $userVolume): void
    {
        $volume = min(100, max(0, $userVolume));
        $this->api->request('PUT', "me/player/volume?volume_percent={$volume}");
    }

    public function getPlayingTrackOrDie(): PlayerInterface
    {
        $currentlyPlaying = Player::fromResponse($this->api->request('GET', 'me/player') ?? (object) []);

        if (!$currentlyPlaying->isPlaying()) {
            throw new NotPlayingException('Player is not playing.');
        }

        if (Player::TRACK !== $currentlyPlaying->getType()) {
            throw new NotPlayingTrackException('Spotify is not playing a Track');
        }

        return $currentlyPlaying;
    }

    public function remainingMicroseconds(PlayerInterface $current): int
    {
        $progress = $current->getProgress();
        $duration = $current->getItem()->getDuration();

        $waitBuffer = 2000;

        return ($duration - $progress + $waitBuffer) * 1000;
    }
}
