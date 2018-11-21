<?php
declare(strict_types=1);


namespace Crawlzone\Policy;


use Psr\Http\Message\RequestInterface;
use function Crawlzone\get_request_depth;

class RequestDepthPolicy
{
    /**
     * @var int
     */
    private $depth;

    public function __construct(int $depth)
    {
        $this->depth = $depth;
    }

    public function isRequestAllowed(RequestInterface $request): bool
    {
        $currentRequestDepth = get_request_depth($request);
        return $currentRequestDepth < $this->depth;
    }
}