<?php
declare(strict_types=1);


namespace Crawlzone\Policy;


use Psr\Http\Message\RequestInterface;
use webignition\RobotsTxt\File\Parser;
use webignition\RobotsTxt\Inspector\Inspector;
use Crawlzone\AbsoluteUri;

class RobotsTxtPolicy implements UriPolicy
{
    private const USER_AGENT = 'crawlzone';

    /**
     * @var string
     */
    private $robotTxtContent;

    /**
     * RobotsTxtPolicy constructor.
     * @param string $robotTxtContent
     */
    public function __construct(string $robotTxtContent)
    {
        $this->robotTxtContent = $robotTxtContent;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function isRequestAllowed(RequestInterface $request): bool
    {
        return $this->isUriAllowed(new AbsoluteUri($request->getUri()));
    }

    /**
     * @return Inspector
     */
    private function getInspector(): Inspector
    {
        $parser = new Parser;
        $parser->setSource($this->robotTxtContent);

        $inspector = new Inspector($parser->getFile());
        $inspector->setUserAgent(self::USER_AGENT);

        return $inspector;
    }

    /**
     * @param AbsoluteUri $uri
     * @return bool
     */
    public function isUriAllowed(AbsoluteUri $uri): bool
    {
        return $this->getInspector()->isAllowed($uri->getValue()->getPath());
    }
}