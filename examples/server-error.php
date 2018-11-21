<?php

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Crawlzone\Client;
use Crawlzone\Event\RequestFailed;
use Crawlzone\Event\ResponseHeadersReceived;
use Crawlzone\Event\ResponseReceived;
use Crawlzone\Event\TransferStatisticReceived;
use Crawlzone\Extension\Extension;
use Crawlzone\Middleware\ResponseMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

$config = [
    'start_uri' => ['https://httpbin.org/status/500','https://httpbin.org/status/404', 'https://httpbin.org/status/200',],
    'concurrency' => 1,
];

$client = new Client($config);

$client->addResponseMiddleware(
    new class implements ResponseMiddleware {
        public function processResponse(ResponseInterface $response, RequestInterface $request): ResponseInterface
        {
            printf("Middleware: %s %s \n", $request->getUri(), $response->getStatusCode());

            return $response;
        }
    }
);

// This extention demostrates the order in which events get dispatched
$client->addExtension(new class() extends Extension {
    public function responseHeadersReceived(ResponseHeadersReceived $event)
    {
        echo "ResponseHeadersReceived: " . $event->getResponse()->getStatusCode() . PHP_EOL;
    }

    public function transferStatisticReceived(TransferStatisticReceived $event)
    {
        echo "TransferStatisticReceived: " . $event->getTransferStats()->getRequest()->getUri() . PHP_EOL;
    }

    public function responseReceived(ResponseReceived $event)
    {
        echo "ResponseReceived: " . $event->getResponse()->getStatusCode() . PHP_EOL;
    }

    public function requestFailed(RequestFailed $event)
    {
        echo "RequestFailed: " . $event->getRequest()->getUri() . PHP_EOL;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ResponseHeadersReceived::class => 'responseHeadersReceived',
            TransferStatisticReceived::class => 'transferStatisticReceived',
            ResponseReceived::class => 'responseReceived',
            RequestFailed::class => 'requestFailed',
        ];
    }
});

$client->run();
