<?php
declare(strict_types=1);

namespace Crawlzone\Config;

/**
 * @package Crawlzone\Config
 * @internal
 */
class FilterOptions
{
    /**
     * @var array
     */
    private $options;

    /**
     * FilterOptions constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function allow(): array
    {
        return $this->get('allow');
    }

    /**
     * @return array
     */
    public function allowDomains(): array
    {
        return $this->get('allow_domains');
    }

    /**
     * @return array
     */
    public function denyDomains(): array
    {
        return $this->get('deny_domains');
    }

    /**
     * @return array
     */
    public function deny(): array
    {
        return $this->get('deny');
    }

    /**
     * @return bool
     */
    public function obeyRobotsTxt(): bool
    {
        return $this->options['robotstxt_obey'];
    }

    /**
     * @param string $name
     * @return array
     */
    private function get(string $name): array
    {
        return $this->options[$name] ?? [];
    }
}
