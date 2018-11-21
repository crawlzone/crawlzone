<?php
declare(strict_types=1);


namespace Crawlzone\Policy;

use Crawlzone\AbsoluteUri;
use Crawlzone\Config\FilterOptions;
use function Crawlzone\is_uri_matched_pattern;

/**
 * @package Crawlzone\Policy
 */
class DenyUri implements UriPolicy
{
    /**
     * @var FilterOptions
     */
    private $filterOptions;

    public function __construct(FilterOptions $filterOptions)
    {
        $this->filterOptions = $filterOptions;
    }

    /**
     * @param AbsoluteUri $uri
     * @return bool
     */
    public function isUriAllowed(AbsoluteUri $uri): bool
    {
        $deniedUriPatterns = $this->filterOptions->deny();

        foreach ($deniedUriPatterns as $pattern) {
            if (is_uri_matched_pattern($uri->getValue(), $pattern)) {
                return false;
            }
        }

        return true;
    }
}
