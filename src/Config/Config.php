<?php
declare(strict_types=1);

namespace Crawlzone\Config;

use Symfony\Component\Config\Definition\Processor;

/**
 * @package Crawlzone\Config
 */
class Config
{
    /**
     * @var array
     */
    private $config;

    /**
     * Config constructor.
     * @param array $config
     */
    private function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $config
     * @return Config
     */
    public static function fromArray(array $config)
    {
        $config = ['crawler' => $config];
        $processor = new Processor();
        $configDefinition = new ConfigDefinition();
        $config = $processor->processConfiguration($configDefinition, $config);

        return new self($config);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->config[$name]);
    }

    /**
     * @return null|FilterOptions
     */
    public function filterOptions(): ? FilterOptions
    {
        return isset($this->config['filter']) ? new FilterOptions($this->config['filter']) : null;
    }

    /**
     * @return array
     */
    public function requestOptions(): array
    {
        return $this->config['request_options'];
    }

    /**
     * @return array
     */
    public function startUris(): array
    {
        return $this->config['start_uri'];
    }

    /**
     * @return int
     */
    public function concurrency(): int
    {
        return $this->config['concurrency'];
    }

    /**
     * @return int
     */
    public function depth(): ? int
    {
        return $this->config['depth'] ?? null;
    }

    /**
     * @return string
     */
    public function saveProgressIn(): string
    {
        return $this->config['save_progress_in'];
    }

    /**
     * @return AutoThrottleOptions
     */
    public function getAutoThrottleOptions(): AutoThrottleOptions
    {
        return new AutoThrottleOptions($this->config['autothrottle']);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->config;
    }
}
