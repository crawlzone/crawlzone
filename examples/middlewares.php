<?php

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Crawlzone\Client;
use Crawlzone\Middleware\RequestMiddleware;
use Crawlzone\Middleware\ResponseMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

$config = [
    'start_uri' => ['https://httpbin.org/ip']
];

$client = new Client($config);

$client->addRequestMiddleware(
    new class implements RequestMiddleware {
        public function processRequest(RequestInterface $request): RequestInterface
        {
            $request = $request->withHeader("User-Agent", "Mybot/1.1");
            printf("Middleware 1 Request: %s \n", $request->getUri());
            return $request;
        }
    }
);

$client->addResponseMiddleware(
    new class implements ResponseMiddleware {
        public function processResponse(ResponseInterface $response, RequestInterface $request): ResponseInterface
        {
            printf("Middleware 2 Response: %s %s \n", $request->getUri(), $response->getStatusCode());
            return $response;
        }
    }
);

$client->run();

/*
Output:
Middleware 1 Request: https://httpbin.org/ip
Middleware 2 Response: https://httpbin.org/ip 200
*/
