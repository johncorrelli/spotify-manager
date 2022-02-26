<?php

namespace App\Commands;

use App\Exceptions\Spotify\NotPlayingException;
use App\Exceptions\Spotify\NotPlayingTrackException;
use App\Exceptions\Spotify\SpotifyException;
use App\Interfaces\Spotify\PlayerInterface;
use App\Models\Spotify\Playable\Track;
use App\Models\Spotify\Spotify;

class Manage
{
    protected Spotify $spotify;

    public function __construct(Spotify $spotify)
    {
        $this->spotify = $spotify;
    }

    public function manage()
    {
        $restartCopy = 'Restart script when Spotify is playing a track.';

        try {
            $player = $this->spotify->getPlayingTrackOrDie();
            $this->manageCurrentTrack($player);
        } catch (NotPlayingException $_) {
            $this->writeToCli("Spotify is not playing. {$restartCopy}");
        } catch (NotPlayingTrackException $_) {
            $this->writeToCli("Only Tracks from Spotify are managed right now. {$restartCopy}");
        } catch (SpotifyException $e) {
            $this->writeToCli($e->getMessage());
        }
    }

    protected function manageCurrentTrack(PlayerInterface $player)
    {
        $track = $player->getItem();

        if (!($track instanceof Track)) {
            throw new NotPlayingTrackException();
        }

        $this->writeToCli('');
        $this->writeToCli('Now playing: ' . $track->getComment());

        $msRemaining = $this->spotify->remainingMicroseconds($player);

        $input = $this->ask($msRemaining);

        if (null === $input) {
            $this->handleNoInput();
            return;
        }

        $this->handleInput($input);
    }

    protected function handleNoInput(): void
    {
        $this->manage();
    }

    protected function handleInput(mixed $input): void
    {
        $commands = $this->getCommands();

        if (
            !is_numeric($input)
            || $input > count($commands)
            || $input < 0
        ) {
            $this->writeToCli('Invalid input received.');
            $this->manage();

            return;
        }

        // @todo handle this better
        if ($commands[$input] !== 'nextTrack') {
            $this->spotify->{$commands[$input]}($this->spotify->getSkippables());
        }

        $this->spotify->nextTrack();

        sleep(1);

        $this->manage();
    }

    protected function getCommands(): array
    {
        return [
            'blockSong',
            'blockArtist',
            'blockAlbum',
            'nextTrack'
        ];
    }

    protected function ask(int $remainingMicroseconds): ?int
    {
        $timeout = ceil($remainingMicroseconds / 1000000);

        $this->writeToCli('Player controls:');
        foreach ($this->getCommands() as $input => $command) {
            $this->writeToCli("    {$input} - to {$command}");
        }

        $input = shell_exec("read -t $timeout -p \"What would you like to do?\n\"; echo \$REPLY");

        $trimmedInput = trim($input ?? '');

        if (!is_numeric($trimmedInput)) {
            return null;
        }

        return (int) $trimmedInput;
    }

    private function writeToCli(string $text, bool $withLineBreak = true)
    {
        echo $text;

        if ($withLineBreak) {
            echo "\n";
        }
    }
}
