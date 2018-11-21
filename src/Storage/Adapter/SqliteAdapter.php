<?php
declare(strict_types=1);

namespace Crawlzone\Storage\Adapter;

use PDO;

/**
 * @package Crawlzone\Storage\Adapter
 */
class SqliteAdapter
{
    private $storage;

    /**
     * @param SqliteDsn $dsn
     */
    public function __construct(SqliteDsn $dsn)
    {
        $this->storage = new PDO($dsn->value());
        $this->storage->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param string $memoryOrFilePath
     * @return SqliteAdapter
     */
    public static function create(string $memoryOrFilePath): self
    {
        return new self(SqliteDsn::fromString($memoryOrFilePath));
    }

    /**
     * @inheritdoc
     */
    public function executeQuery(string $query, array $data = []): bool
    {
        $prepared = $this->storage->prepare($query);

        return $prepared->execute($data);
    }

    /**
     * @inheritdoc
     */
    public function fetchAll(string $query, array $data = []): array
    {
        $prepared = $this->storage->prepare($query);

        $prepared->execute($data);

        return $prepared->fetchAll(PDO::FETCH_ASSOC);
    }

    public function beginTransaction(): void
    {
        $this->storage->beginTransaction();
    }

    public function commit(): void
    {
        $this->storage->commit();
    }
}
