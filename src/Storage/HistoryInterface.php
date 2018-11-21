<?php
declare(strict_types=1);

namespace Crawlzone\Storage;

use Psr\Http\Message\RequestInterface;

/**
 * @package Crawlzone\Storage
 */
interface HistoryInterface
{
    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function contains(RequestInterface $request): bool;

    /**
     * @param RequestInterface $request
     */
    public function add(RequestInterface $request): void;
}
