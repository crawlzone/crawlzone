<?php
declare(strict_types=1);


namespace Crawlzone\Event;

use Exception;
use Psr\Http\Message\RequestInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @package Crawlzone\Event
 */
class RequestFailed extends Event
{
    /**
     * @var Exception
     */
    private $reason;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param Exception $reason
     * @param RequestInterface $request
     */
    public function __construct(Exception $reason, RequestInterface $request)
    {
        $this->reason = $reason;
        $this->request = $request;
    }

    /**
     * @return Exception
     */
    public function getReason(): Exception
    {
        return $this->reason;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
