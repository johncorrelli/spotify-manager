<?php

namespace App\Models\Spotify;

class Spotify
{
    use BlocksCurrent;
    use ControlsPlayer;
    use SkipsTracks;

    /**
     * @var string
     */
    public $userId;

    private Api $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
        $this->setUserId();
    }

    /**
     * Returns the current user.
     *
     * @return object
     */
    protected function me(): object
    {
        return $this->api->request('GET', 'me') ?? (object) [];
    }

    /**
     * Sets the current user id based off of the response from Spotify.
     */
    protected function setUserId(): void
    {
        $this->userId = $this->me()->id;
    }
}
