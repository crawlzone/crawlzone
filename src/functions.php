<?php

namespace Crawlzone;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

const REQUEST_DEPTH_HEADER = 'X-Crawler-Request-Depth';

/**
 * @param ResponseInterface $response
 * @return bool
 */
function is_redirect(ResponseInterface $response): bool
{
    if (substr($response->getStatusCode(), 0, 1) != '3' || !$response->hasHeader('Location')) {
        return false;
    }

    return true;
}

/**
 * @param UriInterface $uri
 * @param string $pattern
 * @return bool
 */
function is_uri_matched_pattern(UriInterface $uri, string $pattern): bool
{
    $pattern = preg_quote($pattern, '/');

    $match = preg_match("/" . $pattern . "/i", (string) $uri);

    if (false === $match) {
        throw new \InvalidArgumentException('Invalid pattern: ' . $pattern);
    }

    return (bool) $match;
}

/**
 * @param RequestInterface $request
 * @return int
 */
function get_request_depth(RequestInterface $request): int
{
    return (int) $request->getHeaderLine(REQUEST_DEPTH_HEADER);
}
