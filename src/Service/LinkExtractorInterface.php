<?php
declare(strict_types=1);

namespace Crawlzone\Service;

use Psr\Http\Message\ResponseInterface;

/**
 * @package Crawlzone\Service
 */
interface LinkExtractorInterface
{
    /**
     * @param ResponseInterface $response
     * @return array
     */
    public function extract(ResponseInterface $response): array;
}
