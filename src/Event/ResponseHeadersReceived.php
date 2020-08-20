<?php
declare(strict_types=1);


namespace Crawlzone\Event;

use Psr\Http\Message\ResponseInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @package Crawlzone\Event
 */
class ResponseHeadersReceived extends Event
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
