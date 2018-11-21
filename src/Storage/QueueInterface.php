<?php
declare(strict_types=1);

namespace Crawlzone\Storage;

use Psr\Http\Message\RequestInterface;

/**
 * @package Crawlzone\Storage
 */
interface QueueInterface
{
    /**
     * @param RequestInterface $request
     */
    public function enqueue(RequestInterface $request): void;

    /**
     * @return RequestInterface
     */
    public function dequeue(): RequestInterface;

    /**
     * @return bool
     */
    public function isEmpty(): bool;
}
