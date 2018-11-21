<?php
declare(strict_types=1);

namespace Crawlzone\Service;

use Crawlzone\Storage\Adapter\SqliteAdapter;

/**
 * @package Crawlzone\Service
 */
class StorageService
{
    /**
     * @var SqliteAdapter
     */
    private $sqliteAdapter;

    /**
     * @param SqliteAdapter $sqliteAdapter
     */
    public function __construct(SqliteAdapter $sqliteAdapter)
    {
        $this->sqliteAdapter = $sqliteAdapter;
    }

    /**
     * @param string $path
     */
    public function importFile(string $path): void
    {
        $data = file_get_contents($path);

        $queries = explode(";\n", $data);

        foreach ($queries as $query) {
            $this->sqliteAdapter->executeQuery($query);
        }
    }
}
