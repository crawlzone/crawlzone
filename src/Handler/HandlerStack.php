<?php
declare(strict_types=1);


namespace Crawlzone\Handler;

use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @package Crawlzone\Handler
 */
class HandlerStack
{
    /**
     * @var \GuzzleHttp\HandlerStack
     */
    private $handlerStack;

    /**
     * @param Handler $handler
     */
    public function __construct(Handler $handler)
    {
        $handlerStack = new \GuzzleHttp\HandlerStack($handler);

        // Initializing GuzzleHttp core middlewares
        $handlerStack->push(Middleware::prepareBody(), 'prepare_body');
        $handlerStack->push(Middleware::cookies(), 'cookies');
        $handlerStack->push(Middleware::redirect(), 'allow_redirects');

        $this->handlerStack = $handlerStack;
    }

    /**
     * @param Handler $handler
     */
    public function setHandler(Handler $handler): void
    {
        $this->handlerStack->setHandler($handler);
    }

    /**
     * Invokes the handler stack as a composed handler
     *
     * @param RequestInterface $request
     * @param array $options
     * @return PromiseInterface
     */
    public function __invoke(RequestInterface $request, array $options): PromiseInterface
    {
        $handler = $this->handlerStack;

        return $handler($request, $options);
    }

    /**
     * @param callable $middleware
     */
    public function push(callable $middleware): void
    {
        $this->handlerStack->push($middleware);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->handlerStack->__toString();
    }
}
