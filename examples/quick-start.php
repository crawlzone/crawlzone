<?php

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Crawlzone\Client;
use Crawlzone\Middleware\ResponseMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

$config = [
    'start_uri' => ['https://httpbin.org/'],
    'concurrency' => 3,
    'filter' => [
        //A list of string containing domains which will be considered for extracting the links.
        'allow_domains' => ['httpbin.org'],
        //A list of regular expressions that the urls must match in order to be extracted.
        'allow' => ['/get','/ip','/anything']
    ]
];

$client = new Client($config);

$client->addResponseMiddleware(
    new class implements ResponseMiddleware {
        public function processResponse(ResponseInterface $response, RequestInterface $request): ResponseInterface
        {
            printf("Process Response: %s %s \n", $request->getUri(), $response->getStatusCode());

            return $response;
        }
    }
);

$client->run();
