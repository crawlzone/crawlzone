<?php
declare(strict_types=1);


namespace Crawlzone\Config;

/**
 * @package Crawlzone\Config
 * @internal
 */
class AutoThrottleOptions
{
    /**
     * @var array
     */
    private $options;

    /**
     * AutoThrottleOptions constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) $this->options['enabled'];
    }

    /**
     * @return int
     */
    public function getMinDelay(): int
    {
        return $this->options['min_delay'];
    }

    /**
     * @return int
     */
    public function getMaxDelay(): int
    {
        return $this->options['max_delay'];
    }
}
