<?php
declare(strict_types=1);

namespace Crawlzone\Storage\Adapter;

/**
 * @package Crawlzone\Storage\Adapter
 */
class SqliteDsn
{
    /**
     * @var string
     */
    private $dsn;

    /**
     * @param string $dsn
     */
    public function __construct(string $dsn = '')
    {
        $this->dsn = $dsn;
        if (empty($this->dsn)) {
            $this->dsn = 'sqlite::memory:';
        }

        $this->guardDsn($this->dsn);
    }

    /**
     * @param string $string
     * @return SqliteDsn
     */
    public static function fromString(string $string): self
    {
        $string = str_replace('sqlite:', '', $string);
        $string = trim($string, ':');

        if ('memory' === $string || empty($string)) {
            return new self;
        }

        return new self('sqlite:' . $string);
    }

    /**
     * @param string $dsn
     */
    private function guardDsn(string $dsn): void
    {
        if (false === strpos($dsn, 'sqlite:')) {
            throw new \RuntimeException('The DSN must be valid SQLite DSN.');
        }
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->dsn;
    }
}
