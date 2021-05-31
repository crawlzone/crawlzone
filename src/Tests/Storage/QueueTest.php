<?php

namespace Crawlzone\Tests\Storage;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Crawlzone\Service\StorageService;
use Crawlzone\Storage\Adapter\SqliteAdapter;
use Crawlzone\Storage\Adapter\SqliteDsn;
use Crawlzone\Storage\Queue;

class QueueTest extends TestCase
{
    private $adapter;

    public function setUp(): void
    {
        parent::setUp();

        $this->adapter = new SqliteAdapter(new SqliteDsn('sqlite::memory:'));
        $storageService = new StorageService($this->adapter);
        $storageService->importFile(__DIR__ . '/../../Storage/Schema/main.sql');
    }

    public function tearDown(): void
    {
        $this->adapter = null;

        parent::tearDown();
    }

    public function testEnqueueAndDequeue()
    {
        $queue = new Queue($this->adapter);

        $method = 'POST';
        $uri = '/test.html';
        $headers = ['content-type' => ['application/x-www-form-urlencoded']];
        $body = 'username=test&password=test';

        $request = new Request(
            $method,
            $uri,
            $headers,
            $body
        );

        $queue->enqueue($request);

        $requestFromQueue = $queue->dequeue();

        $this->assertEquals($method, $requestFromQueue->getMethod());
        $this->assertEquals($uri, (string)$requestFromQueue->getUri());
        $this->assertEquals($headers, $requestFromQueue->getHeaders());
        $this->assertEquals($body, (string) $requestFromQueue->getBody());
    }

    public function testIsEmptyQueue()
    {
        $queue = new Queue($this->adapter);

        $this->assertTrue($queue->isEmpty());

        $queue->enqueue(new Request('GET', '/test.html'));
        $queue->enqueue(new Request('GET', '/test.html'));

        $this->assertFalse($queue->isEmpty());

        $queue->dequeue();

        $this->assertTrue($queue->isEmpty());
    }
}
