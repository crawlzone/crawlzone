<?php
declare(strict_types=1);


namespace Crawlzone;

use Crawlzone\Http\HttpClient;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;


/**
 * @package Crawlzone
 */
class Session
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * @param RequestInterface $request
     * @return PromiseInterface
     */
    public function sendAsync(RequestInterface $request): PromiseInterface
    {
        return $this->httpClient->sendAsync($request);
    }
}
