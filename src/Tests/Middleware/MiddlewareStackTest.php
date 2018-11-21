<?php

namespace Crawlzone\Tests\Middleware;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Crawlzone\Middleware\MiddlewareStack;
use Crawlzone\Middleware\RequestMiddleware;
use Crawlzone\Middleware\ResponseMiddleware;

class MiddlewareStackTest extends TestCase
{
    public function testGetRequestMiddlewareStack()
    {
        $middlewareStack = new MiddlewareStack;

        $middlewareStack->addRequestMiddleware(new class implements RequestMiddleware {
            public function processRequest(RequestInterface $request): RequestInterface
            {
                return $request->withHeader('a-header', "1");
            }
        });

        $middlewareStack->addRequestMiddleware(new class implements RequestMiddleware {
            public function processRequest(RequestInterface $request): RequestInterface
            {
                return $request->withHeader('b-header', "2");
            }
        });

        $middlewareStack->addRequestMiddleware(new class implements RequestMiddleware {
            public function processRequest(RequestInterface $request): RequestInterface
            {
                return $request->withHeader('c-header', "3");
            }
        });

        $request = new Request('GET', '/test');

        /** @var RequestInterface $request */
        $request = $middlewareStack->getRequestMiddlewareStack()($request);

        $this->assertEquals("1", $request->getHeaderLine('a-header'));
        $this->assertEquals("2", $request->getHeaderLine('b-header'));
        $this->assertEquals("3", $request->getHeaderLine('c-header'));
    }

    public function testGetResponseMiddlewareStack()
    {
        $middlewareStack = new MiddlewareStack;

        $middlewareStack->addResponseMiddleware(new class implements ResponseMiddleware {
            public function processResponse(ResponseInterface $response, RequestInterface $request): ResponseInterface
            {
                return $response->withHeader('a-header', "1");
            }
        });

        $middlewareTwo = new class implements ResponseMiddleware {
            private $request;

            public function processResponse(ResponseInterface $response, RequestInterface $request): ResponseInterface
            {
                $this->request = $request;
                return $response->withHeader('b-header', "2");
            }

            public function getRequest(): RequestInterface
            {
                return $this->request;
            }
        };

        $middlewareStack->addResponseMiddleware($middlewareTwo);


        $response = new Response;
        $request = new Request('GET', '/test');

        /** @var RequestInterface $request */
        $request = $middlewareStack->getResponseMiddlewareStack()($response, $request);

        $this->assertEquals("1", $request->getHeaderLine('a-header'));
        $this->assertEquals("2", $request->getHeaderLine('b-header'));
        $this->assertEquals("/test", (string) $middlewareTwo->getRequest()->getUri());
        $this->assertEquals("GET", $middlewareTwo->getRequest()->getMethod());
    }
}
