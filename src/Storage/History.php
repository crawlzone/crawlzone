<?php
declare(strict_types=1);

namespace Crawlzone\Storage;

use Psr\Http\Message\RequestInterface;
use Crawlzone\Service\RequestFingerprint;
use Crawlzone\Storage\Adapter\SqliteAdapter;

/**
 * @package Crawlzone\Storage
 */
class History implements HistoryInterface
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
     * @return bool
     */
    public function contains(RequestInterface $request): bool
    {
        $fingerprint=RequestFingerprint::calculate($request);

        $result = $this->storageAdapter->fetchAll(
            'SELECT `fingerprint` FROM `history` WHERE `fingerprint`=? LIMIT 1',
            [$fingerprint]
        );

        if (empty($result)) {
            return false;
        }

        return true;
    }

    /**
     * @param RequestInterface $request
     */
    public function add(RequestInterface $request): void
    {
        $fingerprint = RequestFingerprint::calculate($request);
        $this->storageAdapter->executeQuery(
            'INSERT OR IGNORE INTO `history` (`fingerprint`) VALUES (?)',
            [$fingerprint]
        );
    }
}
