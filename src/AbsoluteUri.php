<?php
declare(strict_types=1);


namespace Crawlzone;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/**
 * @package Crawlzone
 */
class AbsoluteUri
{
    /**
     * @var Uri
     */
    private $uri;

    /**
     * @param UriInterface $uri
     */
    public function __construct(UriInterface $uri)
    {
        if (! Uri::isAbsolute($uri)) {
            throw new \InvalidArgumentException('URI must be absolute.');
        }

        $this->uri = $uri;
    }

    /**
     * @param string $uri
     * @return AbsoluteUri
     */
    public static function fromString(string $uri): self
    {
        return new self(new Uri($uri));
    }

    /**
     * @return UriInterface
     */
    public function getValue(): UriInterface
    {
        return $this->uri;
    }
}
