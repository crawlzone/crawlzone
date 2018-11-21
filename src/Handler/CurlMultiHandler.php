<?php

namespace Crawlzone\Handler;

use GuzzleHttp\Handler\CurlMultiHandler as GuzzleCurlMultiHandler;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @package Crawlzone\Handler
 */
class CurlMultiHandler implements Handler
{
    /**
     * @var GuzzleCurlMultiHandler
     */
    private $handler;

    /**
     * CurlMultiHandler constructor.
     * @param GuzzleCurlMultiHandler $handler
     */
    public function __construct(GuzzleCurlMultiHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @inheritdoc
     */
    public function execute(): void
    {
        $this->handler->execute();
    }

    /**
     * @inheritdoc
     */
    public function __invoke(RequestInterface $request, array $options): PromiseInterface
    {
        return $this->handler->__invoke($request, $options);
    }
}
