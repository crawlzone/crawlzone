<?php

namespace Crawlzone\Tests\Middleware;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Crawlzone\Client;
use Crawlzone\Handler\Handler;
use Crawlzone\Handler\MockHandler;

class MiddlewareMockHandlerTest extends TestCase
{
    public function testMiddleware()
    {
        $handler = new MockHandler([
            new Response(200, [], '<a href="/test.html">test</a>'),
            new Response(200, [], '<a href="/test1.html">test1</a>'),
            new Response(200, [], '<a href="/test2.html">test2</a>'),
            new Response(200, [], '<a href="/test3.html">test3</a><a href="/test1.html">test1</a><a href="/test.html">test</a><a href="/test2.html">test2</a>'),
        ]);

        $crawler = $this->getClient($handler);

        $history = new HistoryMiddleware;
        $crawler->addRequestMiddleware($history);

        $crawler->run();

        $this->assertEquals([
            'GET http://site1.local/',
            'GET http://site1.local/test.html',
            'GET http://site1.local/test1.html',
            'GET http://site1.local/test2.html',
            'GET http://site1.local/test3.html',
        ], $history->getHistory());
    }

    public function test500Error()
    {
        $handler = new MockHandler([
            new Response(500, ['X-Foo' => 'Bar'], '500 Error'),
        ]);

        $crawler = $this->getClient($handler);

        $history = new HistoryMiddleware;
        $crawler->addRequestMiddleware($history);

        $crawler->run();

        $this->assertEquals([
            'GET http://site1.local/',
        ], $history->getHistory());
    }

    public function test404Error()
    {
        $handler = new MockHandler([
            new Response(404, [], '404 Error'),
        ]);

        $crawler = $this->getClient($handler);
        $history = new HistoryMiddleware;
        $crawler->addRequestMiddleware($history);
        $crawler->run();

        $this->assertEquals([
            'GET http://site1.local/',
        ], $history->getHistory());
    }

    /**
     * @param $handler
     * @return Client
     */
    private function getClient(Handler $handler): Client
    {
        $config = [
            'start_uri' => ['http://site1.local/'],
            'concurrency' => 4
        ];
        $crawler = new Client($config);
        $crawler->setHandler($handler);

        return $crawler;
    }
}
