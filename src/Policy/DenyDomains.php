<?php
declare(strict_types=1);


namespace Crawlzone\Policy;

use Crawlzone\AbsoluteUri;
use Crawlzone\Config\FilterOptions;

/**
 * @package Crawlzone\Policy
 */
class DenyDomains implements UriPolicy
{
    /**
     * @var FilterOptions
     */
    private $filterOptions;

    /**
     * @param FilterOptions $filterOptions
     */
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
        $denyDomains = $this->filterOptions->denyDomains();

        if (in_array($uri->getValue()->getHost(), $denyDomains)) {
            return false;
        }

        return true;
    }
}
