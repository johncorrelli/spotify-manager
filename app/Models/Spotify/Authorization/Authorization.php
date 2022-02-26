<?php

declare(strict_types=1);

namespace App\Models\Spotify\Authorization;

class Authorization
{
    public const RESPONSE_URI = 'https://postman-echo.com/post';

    protected null|string $accessToken;

    protected Api $api;

    protected string $clientId;

    protected null|string $refreshToken;

    public function __construct(string $clientId, ?string $accessToken, ?string $refreshToken, Api $api)
    {
        $this->clientId = $clientId;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->api = $api;
    }

    /**
     * Generates an authorization token for the current user.
     * If the user has never initialized Spotify, it will first
     * take them through the authorization flow.
     */
    public function generateAuthToken(): string
    {
        if (!$this->getAccessToken()) {
            return $this->initializeAuthorization();
        }

        return $this->refreshAccessToken($this->getRefreshToken() ?? '');
    }

    /**
     * Returns the current access token.
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Returns the refresh token, used to generate a new access token.
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * Only needs to run once per user (unless they clear the refresh token).
     * Will generate and access and refresh token for the current user.
     * They will be asked to paste a `code` into the terminal, which we will then
     * use to generate their access and refresh tokens.
     */
    public function initializeAuthorization(): string
    {
        $responseUri = self::RESPONSE_URI;

        echo "Visit: https://accounts.spotify.com/authorize?client_id={$this->clientId}".
            "&redirect_uri={$responseUri}",
            '&scope=playlist-read-collaborative,playlist-modify-public,user-modify-playback-state,user-read-playback-state'.
            '&response_type=code&state=oolala';

        echo "\n\n";

        echo "After authorization, you will be redirected to {$responseUri} \n";
        echo "Copy the `code` from the query hash paste below.\n";
        echo "\n";

        echo 'Code: ';
        $authorizationCode = readline();

        return $this->getAccessTokenFromAuthorizationCode($authorizationCode);
    }

    /**
     * After a user enters the `code`, we will send that off to Spotify to hydrate
     * the access and refresh token. Access tokens last for 1 hour. Refresh tokens are forever.
     */
    protected function getAccessTokenFromAuthorizationCode(string $authorizationCode): string
    {
        $getCredentials = $this->api->request(
            'POST',
            'token',
            [
                'grant_type' => 'authorization_code',
                'code' => $authorizationCode,
                'redirect_uri' => self::RESPONSE_URI,
            ]
        );

        $authorizationAccessToken = $getCredentials->access_token;
        $authorizationRefreshToken = $getCredentials->refresh_token;

        $this->setAccessToken($authorizationAccessToken);
        $this->setRefreshToken($authorizationRefreshToken);

        return $authorizationAccessToken;
    }

    /**
     * Generates a new access token based off of the user's refresh token.
     */
    protected function refreshAccessToken(string $refreshToken): string
    {
        $getCredentials = $this->api->request(
            'POST',
            'token',
            [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]
        );

        $authorizationAccessToken = $getCredentials->access_token;
        $this->setAccessToken($authorizationAccessToken);

        return $authorizationAccessToken;
    }

    /**
     * Sets the new access token.
     */
    protected function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Sets the refresh token.
     */
    protected function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }
}
