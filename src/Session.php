<?php
declare(strict_types=1);


namespace Crawlzone;

use Crawlzone\Http\HttpClient;


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
}
