<?php
declare(strict_types=1);


namespace Crawlzone\Extension;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Crawlzone\Event\BeforeRequestSent;
use Crawlzone\Exception\InvalidRequestException;
use Crawlzone\Policy\RobotsTxtPolicy;

class RobotTxt extends Extension
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var bool
     */
    private $obeyRobotTxt;

    /**
     * RobotTxt constructor.
     * @param bool $obeyRobotTxt
     */
    public function __construct(bool $obeyRobotTxt)
    {
        $this->client = new Client;
        $this->obeyRobotTxt = $obeyRobotTxt;
    }

    /**
     * @param BeforeRequestSent $event
     */
    public function beforeRequestSent(BeforeRequestSent $event): void
    {
        //If the obeyRobotTxt option is not enabled, then skip the logic
        if(! $this->obeyRobotTxt) {
            return;
        }

        $request = $event->getRequest();
        $robotTxtUri = (string) $request->getUri()->withPath('/robots.txt');

        // Checking cache first
        $robotTxtContent = "";
        if (empty($this->cache[$robotTxtUri])) {
            // Getting content of the robots.txt file
            $robotTxtResponse = $this->client->request('GET', $robotTxtUri);

            // Robots.txt file exists
            if ($robotTxtResponse->getStatusCode() === 200) {
                $robotTxtContent = (string) $robotTxtResponse->getBody();

                // Store the content of the robots.txt in the cache
                $this->cache[$robotTxtUri] = $robotTxtContent;
            }
        } else {
            $robotTxtContent = $this->cache[$robotTxtUri];
        }

        // If the content is still empty, then robots.txt doesn't exist or is empty (no rules).
        if (empty($robotTxtContent)) {
            return;
        }

        // Do not do anything if it is allowed
        $policy = new RobotsTxtPolicy($robotTxtContent);
        if ($policy->isRequestAllowed($request)) {
            return;
        }

        // Stopping this request
        throw new InvalidRequestException('The path "' . $request->getUri()->getPath() . '" is not allowed by robots.txt.');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeRequestSent::class => 'beforeRequestSent',
        ];
    }
}