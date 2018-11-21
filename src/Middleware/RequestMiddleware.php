<?php

namespace Crawlzone\Middleware;

use Psr\Http\Message\RequestInterface;

/**
 * @package Crawlzone\Middleware
 */
interface RequestMiddleware
{
    /**
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function processRequest(RequestInterface $request): RequestInterface;
}
