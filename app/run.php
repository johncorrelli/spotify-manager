<?php

namespace App;

require __DIR__.'/../vendor/autoload.php';

use App\Models\Storage\Credentials;
use App\Models\Spotify\Api;
use App\Models\Spotify\Authorization\Api as AuthorizationApi;
use App\Models\Spotify\Spotify;
use App\Models\Spotify\Authorization\Authorization;
use App\Models\Storage\Skippables;

// Setup initial authorization
$credentialsFile = __DIR__.'/../storage/auth.json';
$credentials = new Credentials($credentialsFile);
$credentials->loadOrCreate();

// Setup Spotify user token
$spotifyAuthorizationApi = new AuthorizationApi(
    $credentials->get('SPOTIFY_CLIENT_ID') ?? '',
    $credentials->get('SPOTIFY_CLIENT_SECRET') ?? '',
);
$spotifyAuth = new Authorization(
    $credentials->get('SPOTIFY_CLIENT_ID') ?? '',
    $credentials->get('SPOTIFY_ACCESS_TOKEN') ?? '',
    $credentials->get('SPOTIFY_REFRESH_TOKEN') ?? '',
    $spotifyAuthorizationApi
);

$spotifyAuthToken = $spotifyAuth->generateAuthToken();

// Store Spotify Authorization
$credentials->set('SPOTIFY_ACCESS_TOKEN', $spotifyAuth->getAccessToken() ?? '');
$credentials->set('SPOTIFY_REFRESH_TOKEN', $spotifyAuth->getRefreshToken() ?? '');

$spotifyApi = new Api($spotifyAuthToken);
$skippables = new Skippables(__DIR__.'/../storage/Skippables.json');
$spotify = new Spotify($spotifyApi);
$spotify->setSkippables($skippables);

if (!isset($argv[1])) {
    $spotify->watchPlayer();
}

if (isset($argv[1])) {
    $command = $argv[1];

    if ($command === 'block-song') {
        $spotify->blockSong($skippables);
    }

    if ($command === 'block-artist') {
        $spotify->blockArtist($skippables);
    }

    if ($command === 'block-album') {
        $spotify->blockAlbum($skippables);
    }
}
