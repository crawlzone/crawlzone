<?php

namespace Crawlzone\Tests\Service;

use PHPUnit\Framework\TestCase;
use Crawlzone\AbsoluteUri;
use Crawlzone\Config\FilterOptions;
use Crawlzone\Policy\AggregateUriPolicy;

class AggregateUriPolicyTest extends TestCase
{
    public function testAllowUri()
    {
        $filter = [
            'allow' => ['/test/page.html']
        ];

        $filter = new FilterOptions($filter);

        $policy = new AggregateUriPolicy($filter);

        $this->assertTrue($policy->isUriAllowed(AbsoluteUri::fromString('http://example.com/test/page.html')));
        $this->assertFalse($policy->isUriAllowed(AbsoluteUri::fromString('http://example.com/page.html')));
    }

    public function testDenyUri()
    {
        $filter = [
            'deny' => ['/test/page.html']
        ];

        $filter = new FilterOptions($filter);

        $policy = new AggregateUriPolicy($filter);

        $this->assertFalse($policy->isUriAllowed(AbsoluteUri::fromString('http://example.com/test/page.html')));
        $this->assertTrue($policy->isUriAllowed(AbsoluteUri::fromString('http://example.com/page.html')));
    }

    public function testAllowDomainsUri()
    {
        $filter = [
            'allow_domains' => ['example.com']
        ];

        $filter = new FilterOptions($filter);

        $policy = new AggregateUriPolicy($filter);

        $this->assertTrue($policy->isUriAllowed(AbsoluteUri::fromString('http://example.com/test/page.html')));
        $this->assertFalse($policy->isUriAllowed(AbsoluteUri::fromString('http://www.example.com/page.html')));
    }

    public function testDenyDomainsUri()
    {
        $filter = [
            'deny_domains' => ['example.com']
        ];

        $filter = new FilterOptions($filter);

        $policy = new AggregateUriPolicy($filter);

        $this->assertFalse($policy->isUriAllowed(AbsoluteUri::fromString('http://example.com/test/page.html')));
        $this->assertTrue($policy->isUriAllowed(AbsoluteUri::fromString('http://www.example.com/page.html')));
    }
}
