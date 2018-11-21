<?php

namespace Crawlzone\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @package Crawlzone\Middleware
 */
interface ResponseMiddleware
{
    /**
     * @param ResponseInterface $response
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function processResponse(ResponseInterface $response, RequestInterface $request): ResponseInterface;
}
