<?php
/**
 * This example shows how to use Crawlzone with Puppeteer to crawl javascript pages.
 * The disadvantage of this approach is that the script does two requests instead of one.
 *
 * Prerequisites:
 * Install node https://nodejs.org/
 * Install Puppeteer `npm i -g puppeteer` https://github.com/GoogleChrome/puppeteer:
 * Install Symfony Process library `composer require symfony/process`
 * Put browser.js in the same folder as your PHP script
 */
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Process\Process;
use Crawlzone\Client;
use Crawlzone\Middleware\ResponseMiddleware;


require_once __DIR__ . '/../vendor/autoload.php';

$config = [
    'start_uri' => ['http://localhost:8880/javascript/']
];

$client = new Client($config);

$client->addResponseMiddleware(
    new class implements ResponseMiddleware {
        public function processResponse(ResponseInterface $response, RequestInterface $request): ResponseInterface
        {
            $arguments = json_encode([
                'uri' => (string) $request->getUri()
            ]);

            $process = (new Process("node " . __DIR__ . "/browser.js " . escapeshellarg($arguments)))->setTimeout(30);

            $process->run();

            if ($process->isSuccessful()) {
                $content = $process->getOutput();
                $response = $response->withBody(\GuzzleHttp\Psr7\stream_for($content));
            }

            return $response;
        }
    }
);

$client->addResponseMiddleware(
    new class implements ResponseMiddleware {
        public function processResponse(ResponseInterface $response, RequestInterface $request): ResponseInterface
        {
            printf("URI: %s, Status: %s, Body:\n %s\n", $request->getUri(), $response->getStatusCode(), $response->getBody());

            return $response;
        }
    }
);

$client->run();

