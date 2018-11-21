<?php
declare(strict_types=1);


namespace Crawlzone\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @package Crawlzone\Middleware
 */
class MiddlewareStack
{
    /**
     * @var array
     */
    private $requestMiddleware = [];

    /**
     * @var array
     */
    private $responseMiddleware = [];

    /**
     * @var callable
     */
    private $cachedRequestMiddlewareStack;

    /**
     * @var callable
     */
    private $cachedResponseMiddlewareStack;

    /**
     * @param RequestMiddleware $requestMiddleware
     */
    public function addRequestMiddleware(RequestMiddleware $requestMiddleware): void
    {
        $this->requestMiddleware[] = $requestMiddleware;
    }

    /**
     * @param ResponseMiddleware $responseMiddleware
     */
    public function addResponseMiddleware(ResponseMiddleware $responseMiddleware): void
    {
        $this->responseMiddleware[] = $responseMiddleware;
    }

    /**
     * @return callable
     */
    public function getRequestMiddlewareStack(): callable
    {
        if (! $this->cachedRequestMiddlewareStack) {
            $prev = function (RequestInterface $request): RequestInterface {
                return $request;
            };

            /** @var RequestMiddleware $middleware */
            foreach (array_reverse($this->requestMiddleware) as $middleware) {
                $prev = function (RequestInterface $request) use ($middleware, $prev): RequestInterface {
                    return $prev($middleware->processRequest($request));
                };
            }

            $this->cachedRequestMiddlewareStack = $prev;
        }

        return $this->cachedRequestMiddlewareStack;
    }

    /**
     * @return callable
     */
    public function getResponseMiddlewareStack(): callable
    {
        if (! $this->cachedResponseMiddlewareStack) {
            $prev = function (ResponseInterface $response, RequestInterface $request): ResponseInterface {
                return $response;
            };

            /** @var ResponseMiddleware $middleware */
            foreach (array_reverse($this->responseMiddleware) as $middleware) {
                $prev = function (ResponseInterface $response, RequestInterface $request) use ($middleware, $prev): ResponseInterface {
                    return $prev($middleware->processResponse($response, $request), $request);
                };
            }

            $this->cachedResponseMiddlewareStack = $prev;
        }

        return $this->cachedResponseMiddlewareStack;
    }
}
