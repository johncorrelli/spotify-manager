<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\HttpException;

class BaseApi
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * Initialize any headers that you need to send with every request.
     *
     * @var array
     */
    protected $defaultHeaders;

    /**
     * Sets the POST request content-type.
     *
     * @var string
     */
    protected $postBodyFormat;

    public function __construct(array $defaultHeaders = [])
    {
        $this->defaultHeaders = $defaultHeaders;
        $this->postBodyFormat = 'text/json';
    }

    /**
     * Depending on the $bodyFormat, format the request body accordingly.
     */
    public function formatPostBody(string $bodyFormat, array $body = [])
    {
        $this->defaultHeaders[] = "Content-Type: {$bodyFormat}";

        if ('text/json' === $bodyFormat) {
            return json_encode($body);
        }
        if ('application/x-www-form-urlencoded' === $bodyFormat) {
            return http_build_query($body);
        }

        return $body;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Trigger your API requests.
     */
    public function request(string $method, string $url, array $body = [], array $additionalHeaders = []): ?object
    {
        $request = curl_init($this->baseUrl.'/'.$url);
        $headers = array_merge($this->defaultHeaders, $additionalHeaders);

        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_ENCODING, 1);

        if ('POST' === $method || 'PUT' === $method) {
            $postBody = $this->formatPostBody($this->postBodyFormat, $body);
            curl_setopt($request, CURLOPT_POST, 1);
            curl_setopt($request, CURLOPT_POSTFIELDS, $postBody);

            if ('PUT' === $method) {
                curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'PUT');
            }
        }

        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);

        $responseBody = curl_exec($request);

        if (\is_array($request) && $request['http_code'] >= 400) {
            throw new HttpException();
        }

        return json_decode($responseBody);
    }

    /**
     * Sets the authorization for every API request.
     */
    public function setAuthorization(string $authHeader): void
    {
        $this->defaultHeaders[] = $authHeader;
    }

    /**
     * Sets the $baseUrl for every API request.
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Set the format used to post requests.
     */
    public function setPostBodyFormat(string $format): void
    {
        $this->postBodyFormat = $format;
    }
}
