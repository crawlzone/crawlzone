<?php
declare(strict_types=1);


namespace Crawlzone\Policy;

use Crawlzone\AbsoluteUri;
use Crawlzone\Config\FilterOptions;

/**
 * @package Crawlzone\Policy
 */
class AllowDomains implements UriPolicy
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
        $allowedDomains = $this->filterOptions->allowDomains();

        if (! empty($allowedDomains)) {
            return in_array($uri->getValue()->getHost(), $allowedDomains);
        }

        return true;
    }
}
