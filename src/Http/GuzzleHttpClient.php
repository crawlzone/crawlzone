<?php
declare(strict_types=1);


namespace Crawlzone\Http;


use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

class GuzzleHttpClient implements HttpClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * GuzzleHttpClient constructor.
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param RequestInterface $request
     * @return PromiseInterface
     */
    public function sendAsync(RequestInterface $request): PromiseInterface
    {
        return $this->client->sendAsync($request);
    }
}