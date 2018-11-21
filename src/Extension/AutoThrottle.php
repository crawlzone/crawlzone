<?php
declare(strict_types=1);


namespace Crawlzone\Extension;

use GuzzleHttp\TransferStats;
use Crawlzone\Config\AutoThrottleOptions;
use Crawlzone\Event\TransferStatisticReceived;

/**
 * @package Crawlzone\Extension
 */
class AutoThrottle extends Extension
{
    private $previousDelay = 0;

    /**
     * @param TransferStatisticReceived $event
     */
    public function transferStatisticReceived(TransferStatisticReceived $event): void
    {
        $config = $this->getConfig();

        $autoThrottleOptions = $config->getAutoThrottleOptions();

        if (! $autoThrottleOptions->isEnabled()) {
            return;
        }

        $this->delay($autoThrottleOptions, $event->getTransferStats(), $config->concurrency());
    }

    /**
     * @param AutoThrottleOptions $autoThrottleOptions
     * @param TransferStats $transferStats
     * @param int $concurrency
     */
    private function delay(AutoThrottleOptions $autoThrottleOptions, TransferStats $transferStats, int $concurrency): void
    {
        $minDelay = $autoThrottleOptions->getMinDelay();
        $maxDelay = $autoThrottleOptions->getMaxDelay();
        $latency = $transferStats->getTransferTime();

        // If a server needs `latency` seconds to respond then
        // we should send a request each `latency/N` seconds
        // to have N requests processed in parallel
        $targetDelay = $latency / $concurrency;

        // Adjust the delay to make it closer to targetDelay
        // Delay for next requests is set to the average of previous delay and the target delay
        $newDelay = $targetDelay;
        if ($this->previousDelay) {
            $newDelay = ($targetDelay + $this->previousDelay) / 2;
        }

        // If target delay is greater than mean delay, then use it instead of mean.
        // It works better with problematic sites.
        $newDelay = max($targetDelay, $newDelay);

        // Make sure minDelay <= newDelay <= maxDelay
        $newDelay = min(max($minDelay, $newDelay), $maxDelay);

        // Dont adjust delay if response status != 200 and new delay is smaller
        // than old one, as error pages (and redirections) are usually small and
        // so tend to reduce latency, thus provoking a positive feedback by
        // reducing delay instead of increase.
        if ($transferStats->hasResponse() && $transferStats->getResponse()->getStatusCode() !== 200 && $newDelay < $this->previousDelay) {
            $newDelay = $this->previousDelay;
        }

        // Store delay for next iterations
        $this->previousDelay = $newDelay;

        // Get microseconds and round fractions up
        $newDelay = (int) ceil($newDelay * (10 ** 6));

        // Delay for new delay microseconds
        usleep($newDelay);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            TransferStatisticReceived::class => 'transferStatisticReceived'
        ];
    }
}
