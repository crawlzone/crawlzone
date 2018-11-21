<?php

namespace Crawlzone\Tests\Middleware;

use Exception;
use Psr\Http\Message\RequestInterface;
use Crawlzone\Middleware\BaseMiddleware;
use Crawlzone\Middleware\RequestMiddleware;

class HistoryMiddleware implements RequestMiddleware
{
    private $history = [];

    public function processRequest(RequestInterface $request): RequestInterface
    {
        $stream = $request->getBody();

        $requestBody = (string) $stream;

        $stream->rewind();

        $history = trim($request->getMethod() . " " . (string) $request->getUri() . " " . $requestBody);

        $this->history[] = $history;

        return $request;
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    public function processFailure(RequestInterface $request, Exception $reason): Exception
    {
        $reasonMessage = $reason->getMessage();

        echo "\n" . (string) $request->getUri() . " " .$reasonMessage . "\n";

        return $reason;
    }
}
