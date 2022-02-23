<?php

namespace App\Models\Spotify;

use App\Models\BaseApi;

class Api extends BaseApi
{
    /**
     * @param string $authToken your temporary authorization token for connecting to Spotify
     * @param ...$attrs
     */
    public function __construct(string $authToken, ...$attrs)
    {
        parent::__construct(...$attrs);

        $this->setBaseUrl('https://api.spotify.com/v1');
        $this->setAuthorization("Authorization: Bearer {$authToken}");
    }
}
