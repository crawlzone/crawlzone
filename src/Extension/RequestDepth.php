<?php
declare(strict_types=1);


namespace Crawlzone\Extension;


use Crawlzone\Event\BeforeRequestSent;
use Crawlzone\Exception\InvalidRequestException;
use Crawlzone\Policy\RequestDepthPolicy;

class RequestDepth extends Extension
{
    /**
     * @var int
     */
    private $depth;

    /**
     * RequestDepth constructor.
     * @param int|null $depth
     */
    public function __construct(? int $depth)
    {
        $this->depth = $depth;
    }

    /**
     * @param BeforeRequestSent $event
     */
    public function beforeRequestSent(BeforeRequestSent $event): void
    {
        $request = $event->getRequest();

        if ($this->depth) {
            $policy = new RequestDepthPolicy($this->depth);
            if (! $policy->isRequestAllowed($request)) {
                throw new InvalidRequestException('The crawl depth is reached');
            }
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeRequestSent::class => 'beforeRequestSent',
        ];
    }
}