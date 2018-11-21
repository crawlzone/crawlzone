<?php
declare(strict_types=1);

namespace Crawlzone\Storage;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Crawlzone\Service\RequestFingerprint;
use Crawlzone\Storage\Adapter\SqliteAdapter;

/**
 * @package Crawlzone\Storage
 */
class Queue implements QueueInterface
{
    /**
     * @var SqliteAdapter
     */
    private $storageAdapter;

    /**
     * @param SqliteAdapter $storageAdapter
     */
    public function __construct(SqliteAdapter $storageAdapter)
    {
        $this->storageAdapter = $storageAdapter;
    }

    /**
     * @param RequestInterface $request
     */
    public function enqueue(RequestInterface $request): void
    {
        $fingerprint = RequestFingerprint::calculate($request);

        $data = $this->serializeRequest($request);

        $this->storageAdapter->executeQuery(
            'INSERT OR IGNORE INTO `queue` (`fingerprint`,`data`) VALUES (?,?)',
            [$fingerprint, $data]
        );
    }

    /**
     * @return RequestInterface
     */
    public function dequeue(): RequestInterface
    {
        $this->storageAdapter->beginTransaction();

        $data = $this->storageAdapter->fetchAll('SELECT `fingerprint`,`data` FROM `queue` ORDER BY ROWID ASC LIMIT 1');

        $this->storageAdapter->executeQuery('DELETE FROM `queue` WHERE `fingerprint`=?', [$data[0]['fingerprint']]);

        $this->storageAdapter->commit();

        return $this->deserializeRequest($data[0]['data']);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        $data = $this->storageAdapter->fetchAll('SELECT fingerprint FROM `queue` LIMIT 1');

        if (empty($data)) {
            return true;
        }

        return false;
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    private function serializeRequest(RequestInterface $request): string
    {
        $data = [
            'uri' => (string) $request->getUri(),
            'method' => $request->getMethod(),
            'headers' => $request->getHeaders(),
            'body' => (string) $request->getBody()
        ];

        return \GuzzleHttp\json_encode($data);
    }

    /**
     * @param string $jsonData
     * @return RequestInterface
     */
    private function deserializeRequest(string $jsonData): RequestInterface
    {
        $data = \GuzzleHttp\json_decode($jsonData, true);

        return new Request($data['method'], $data['uri'], $data['headers'], $data['body']);
    }
}
