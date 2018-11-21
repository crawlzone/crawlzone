<?php

namespace Crawlzone\Policy;

use Crawlzone\AbsoluteUri;

/**
 * @package Crawlzone\Policy
 */
interface UriPolicy
{
    /**
     * @param AbsoluteUri $uri
     * @return bool
     */
    public function isUriAllowed(AbsoluteUri $uri): bool;
}
