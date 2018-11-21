<?php

namespace Crawlzone\Event;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @package Crawlzone\Event
 */
class ResponseReceived extends Event
{
    /**
     * @var ResponseInterface
     */
    private $response;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param ResponseInterface $response
     * @param RequestInterface $request
     */
    public function __construct(ResponseInterface $response, RequestInterface $request)
    {
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
