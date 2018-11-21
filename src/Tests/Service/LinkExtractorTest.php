<?php

namespace Crawlzone\Tests\Service;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Crawlzone\Config\FilterOptions;
use Crawlzone\Service\LinkExtractor;

class LinkExtractorTest extends TestCase
{
    public function testLinkExtractorFromConfig()
    {
        $extractor = new LinkExtractor;

        $response = new Response(
            200,
            [],
            '<a href="/test">test</a>'
            . '<a href="http://www.test.com/test1">test</a>'
            . '<a href="http://test.com/test2">test</a>'
            . '<a href="http://otherdomain.com/otherdomain">otherdomain</a>'
            . '<a href="/logout">logout</a>'
        );

        $links = $extractor->extract($response);

        $this->assertEquals([
            '/test',
            'http://www.test.com/test1',
            'http://test.com/test2',
            'http://otherdomain.com/otherdomain',
            '/logout'
        ], $links);
    }
}
