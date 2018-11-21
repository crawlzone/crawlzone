<?php
declare(strict_types=1);

namespace Crawlzone\Service;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @package Crawlzone\Service
 */
class LinkExtractor implements LinkExtractorInterface
{
    /**
     * @param ResponseInterface $response
     * @return array
     */
    public function extract(ResponseInterface $response): array
    {
        $stream = $response->getBody();

        $content =  (string) $stream;

        $stream->rewind();

        $crawler = new Crawler($content);

        $elements = $crawler->filterXPath('(//a | //area)');

        $uriCollection = [];

        if (! empty($elements)) {
            /** @var \DOMElement $element */
            foreach ($elements as $element) {
                $href = (string) $element->getAttribute('href');
                $uri = new Uri($href);

                //Ignore anchors
                if ($this->isAnchor($uri)) {
                    continue;
                }

                $uriCollection[] = $href;
            }
        }
        return $uriCollection;
    }

    /**
     * @param UriInterface $uri
     * @return bool
     */
    private function isAnchor(UriInterface $uri): bool
    {
        $link = $uri->__toString();
        if (isset($link[0]) && $link[0] === '#') {
            return true;
        }

        return false;
    }
}
