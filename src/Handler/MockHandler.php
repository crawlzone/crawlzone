<?php

namespace Crawlzone\Handler;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise;

class MockHandler implements Handler
{
    /**
     * @var \GuzzleHttp\Handler\MockHandler
     */
    private $handler;

    /**
     * MockHandler constructor.
     * @param array $queue
     */
    public function __construct(array $queue)
    {
        $this->handler = new \GuzzleHttp\Handler\MockHandler($queue);
    }

    /**
     * @inheritdoc
     */
    public function execute(): void
    {
        Promise\queue()->run();
    }

    /**
     * @inheritdoc
     */
    public function __invoke(RequestInterface $request, array $options): PromiseInterface
    {
        return $this->handler->__invoke($request, $options);
    }
}
