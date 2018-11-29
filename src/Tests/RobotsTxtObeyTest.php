<?php
namespace Crawlzone\Tests;

use PHPUnit\Framework\TestCase;
use Crawlzone\Client;
use Crawlzone\Tests\Middleware\HistoryMiddleware;

class RobotsTxtObeyTest extends TestCase
{
    public function testRobotsTxt()
    {
        $config = [
            'start_uri' => ['http://site1.local/robotstxt.html'],
            'concurrency' => 1,
            'request_options' => [
                'debug' => false,
            ],
            'filter' => [
                'robotstxt_obey' => true
            ]
        ];
        $client = new Client($config);

        $history = new HistoryMiddleware;

        $client->addRequestMiddleware($history);

        $client->run();

        $expected = [
            'GET http://site1.local/robotstxt.html',
            'GET http://site1.local/deny/this-is-allowed.html',
            'GET http://site2.local/',
            'GET http://site2.local/service.html',
            'GET http://site2.local/contacts.html'
        ];

        $this->assertEquals($expected, $history->getHistory());
    }
}
