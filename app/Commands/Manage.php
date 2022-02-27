<?php

declare(strict_types=1);

namespace App\Commands;

use App\Commands\Helpers\ManageHelpers;
use App\Exceptions\Spotify\NotPlayingException;
use App\Exceptions\Spotify\NotPlayingTrackException;
use App\Exceptions\Spotify\SpotifyException;
use App\Interfaces\Spotify\PlayerInterface;
use App\Models\Spotify\Playable\Track;
use App\Models\Spotify\Spotify;

class Manage
{
    use ManageHelpers;

    protected Spotify $spotify;

    public function __construct(Spotify $spotify)
    {
        $this->spotify = $spotify;
    }

    public function manage(): int
    {
        try {
            $player = $this->spotify->getPlayingTrackOrDie();
            $this->manageCurrentTrack($player);
        } catch (NotPlayingException $_) {
            $this->writeToCli('Spotify is not playing. Waiting 5 minutes before trying again.');
            $this->runAfterDelay(fn () => $this->manage(), 5 * 60);
        } catch (NotPlayingTrackException $_) {
            $this->writeToCli('Only Tracks from Spotify are managed right now. Waiting 5 minutes before trying again.');
            $this->runAfterDelay(fn () => $this->manage(), 5 * 60);
        } catch (SpotifyException $e) {
            $this->writeToCli($e->getMessage());
        }

        return 0;
    }

    protected function manageCurrentTrack(PlayerInterface $player): void
    {
        $track = $player->getItem();

        if (!($track instanceof Track)) {
            throw new NotPlayingTrackException();
        }

        if ($this->spotify->isSkippable($track)) {
            $this->skipCurrentTrack();

            return;
        }

        $this->writeToCli('');
        $this->writeToCli('');
        $this->writeToCli('Now playing: '.$track->getComment());

        $msRemaining = $this->spotify->remainingMicroseconds($player);

        $input = $this->ask($msRemaining);

        if (null === $input) {
            $this->handleNoInput();

            return;
        }

        $this->handleInput($input);
    }

    protected function skipCurrentTrack(): void
    {
        $this->spotify->nextTrack();
        $this->runAfterDelay(fn () => $this->manage());
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
            || $input > \count($commands)
            || $input < 0
        ) {
            $this->writeToCli('Invalid input received.');
            $this->manage();

            return;
        }

        $selectedCommand = $commands[$input];
        $selectedCommand->onExecute();
        $this->runAfterDelay(fn () => $this->manage());
    }

    protected function ask(int $remainingMicroseconds): ?int
    {
        $timeout = ceil($remainingMicroseconds / 1000000);

        $this->writeToCli('Player controls:');
        $this->writeToCli('    id : command');
        $this->writeToCli('    -- : -------');
        foreach ($this->getCommands() as $input => $command) {
            $inputId = str_pad((string) $input, 2, ' ', STR_PAD_LEFT);
            $this->writeToCli("    {$inputId} : {$command->getSignature()}");
        }

        /**
         * Use of `shell_exec` is to allow us to pass a timeout though the `-t` option that returns
         * if the user has not supplied input. We're able to take the remaining seconds from the
         * current track and pass that into the `read` command to allow user input during the duration
         * of the current track. Once the track is complete (the timeout is reached) the user's response
         * or lack of response is provided to the calling function.
         */
        $input = shell_exec("read -t {$timeout} -p \"Enter command number if you wish to take action: \"; echo \$REPLY");

        $trimmedInput = trim($input ?? '');

        if (!is_numeric($trimmedInput)) {
            return null;
        }

        return (int) $trimmedInput;
    }

    private function runAfterDelay(callable $callback, int $delay = 1): void
    {
        sleep(abs($delay));

        ($callback)();
    }

    private function writeToCli(string $text, bool $withLineBreak = true): void
    {
        echo $text;

        if ($withLineBreak) {
            echo "\n";
        }
    }
}
