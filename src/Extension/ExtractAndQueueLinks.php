<?php
declare(strict_types=1);

namespace Crawlzone\Extension;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Psr\Http\Message\RequestInterface;
use Crawlzone\AbsoluteUri;
use Crawlzone\Event\ResponseReceived;
use Crawlzone\Policy\UriPolicy;
use Crawlzone\Service\LinkExtractorInterface;
use Crawlzone\Storage\QueueInterface;
use const Crawlzone\REQUEST_DEPTH_HEADER;
use function Crawlzone\get_request_depth;

/**
 * @package Crawlzone\Extension
 */
class ExtractAndQueueLinks extends Extension
{
    /**
     * @var LinkExtractorInterface
     */
    private $linkExtractor;

    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * @var UriPolicy
     */
    private $policy;
    /**
     * @var int|null
     */
    private $depth;

    /**
     * @param LinkExtractorInterface $linkExtractor
     * @param UriPolicy $policy
     * @param int|null $depth
     */
    public function __construct(LinkExtractorInterface $linkExtractor, UriPolicy $policy, ? int $depth)
    {
        $this->linkExtractor = $linkExtractor;
        $this->policy = $policy;
        $this->depth = $depth;
    }

    /**
     * @param ResponseReceived $event
     */
    public function responseReceived(ResponseReceived $event): void
    {
        $response = $event->getResponse();
        $currentRequest = $event->getRequest();

        $links = $this->linkExtractor->extract($response);
        $currentUri = $currentRequest->getUri();

        foreach ($links as $extractedLink) {
            $nextUriToVisit = UriResolver::resolve($currentUri, new Uri($extractedLink));

            if (! $this->policy->isUriAllowed(new AbsoluteUri($nextUriToVisit))) {
                continue;
            }

            $nextRequest = new Request('GET', $nextUriToVisit);

            // @todo: Add option to controll Referer header
            // Add referer header for logging purposes
            /** @var Request $nextRequest */
            $nextRequest = $nextRequest->withHeader('Referer', (string) $currentUri);

            $nextRequest = $this->trackRequestDepth($currentRequest, $nextRequest);

            $this->getQueue()->enqueue($nextRequest);
        }
    }

    /**
     * @param $request
     * @param $nextRequest
     * @return RequestInterface
     */
    private function trackRequestDepth(RequestInterface $request, RequestInterface $nextRequest): RequestInterface
    {
        if ($this->depth) {
            $currentRequestDepth = get_request_depth($request);
            $nextRequest = $this->addRequestDepthHeader($currentRequestDepth, $nextRequest);
        }

        return $nextRequest;
    }

    private function addRequestDepthHeader(int $currentRequestDepth, RequestInterface $request): RequestInterface
    {
        $nextRequestDepth = $currentRequestDepth + 1;

        $request = $request->withHeader(REQUEST_DEPTH_HEADER, $nextRequestDepth);

        return $request;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ResponseReceived::class => 'responseReceived'
        ];
    }
}
