<?php

namespace App\Models\Spotify\Authorization;

use App\Models\BaseApi;

class Api extends BaseApi
{
    /**
     * @param string $clientId your spotify app's client id
     * @param string $clientSecret your spotify app's client secret
     * @param ...$attrs
     */
    public function __construct(string $clientId, string $clientSecret, ...$attrs)
    {
        parent::__construct(...$attrs);

        $this->setBaseUrl('https://accounts.spotify.com/api');
        $this->setPostBodyFormat('application/x-www-form-urlencoded');

        $authorization = base64_encode("{$clientId}:{$clientSecret}");
        $this->setAuthorization("Authorization: Basic {$authorization}");
    }
}
