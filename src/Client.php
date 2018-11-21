<?php
namespace Crawlzone;

use GuzzleHttp\Handler\CurlMultiHandler as GuzzleCurlMultiHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Crawlzone\Config\Config;
use Crawlzone\Event\BeforeEngineStarted;
use Crawlzone\Event\ResponseHeadersReceived;
use Crawlzone\Event\TransferStatisticReceived;
use Crawlzone\Extension\AutoThrottle;
use Crawlzone\Extension\Extension;
use Crawlzone\Extension\ExtractAndQueueLinks;
use Crawlzone\Extension\RedirectScheduler;
use Crawlzone\Extension\RequestDepth;
use Crawlzone\Extension\RobotTxt;
use Crawlzone\Extension\Storage;
use Crawlzone\Handler\CurlMultiHandler;
use Crawlzone\Handler\Handler;
use Crawlzone\Handler\HandlerStack;
use Crawlzone\Http\GuzzleHttpClient;
use Crawlzone\Http\HttpClient;
use Crawlzone\Middleware\MiddlewareStack;
use Crawlzone\Middleware\RequestMiddleware;
use Crawlzone\Middleware\ResponseMiddleware;
use Crawlzone\Policy\AggregateUriPolicy;
use Crawlzone\Service\LinkExtractor;
use Crawlzone\Service\StorageService;
use Crawlzone\Storage\Adapter\SqliteAdapter;
use Crawlzone\Storage\History;
use Crawlzone\Storage\HistoryInterface;
use Crawlzone\Storage\Queue;
use Crawlzone\Storage\QueueInterface;

/**
 * @package Crawlzone
 */
class Client
{
    /**
     * @var Scheduler
     */
    private $scheduler;

    /**
     * @var array
     */
    private $config;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var SqliteAdapter
     */
    private $storageAdapter;

    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * @var HandlerStack
     */
    private $handlerStack;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var HistoryInterface
     */
    private $history;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var array
     */
    private $extentions = [];

    /**
     * @var Session
     */
    private $session;

    /**
     * @var MiddlewareStack
     */
    private $middlewareStack;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->initializeConfig($config);
        $this->initializeStorageAdapter();
        $this->initializeQueue();
        $this->initializeHistory();
        $this->initializeEventDispatcher();
        $this->initializeHandlerStack();
        $this->initializeHttpClient();
        $this->initializeSession();
        $this->initializeDefaultExtentions();
        $this->initializeMiddlewareStack();
        $this->initializeScheduler();
    }

    /**
     * @param array $config
     */
    private function initializeConfig(array $config)
    {
        $this->config = Config::fromArray($config);
    }

    /**
     * @return array
     */
    private function getConfig(): Config
    {
        return $this->config;
    }

    private function initializeStorageAdapter(): void
    {
        $config = $this->getConfig();
        $dsn = $config->saveProgressIn();

        $this->storageAdapter = SqliteAdapter::create($dsn);
    }

    private function getStorageAdapter(): SqliteAdapter
    {
        return $this->storageAdapter;
    }

    private function initializeQueue(): void
    {
        $this->queue = new Queue($this->getStorageAdapter());
    }

    private function getQueue(): QueueInterface
    {
        return $this->queue;
    }

    private function initializeHistory(): void
    {
        $this->history = new History($this->getStorageAdapter());
    }

    /**
     * @return HistoryInterface
     */
    private function getHistory(): HistoryInterface
    {
        return $this->history;
    }

    public function setHandler(Handler $handler): void
    {
        $this->handler = $handler;

        $this->getHandlerStack()->setHandler($this->handler);
    }

    /**
     * @return Handler
     */
    private function getHandler(): Handler
    {
        if (null === $this->handler) {
            return new CurlMultiHandler(new GuzzleCurlMultiHandler);
        }

        return $this->handler;
    }

    private function initializeHandlerStack(): void
    {
        $stack = new HandlerStack($this->getHandler());

        $this->handlerStack = $stack;
    }

    /**
     * @return HandlerStack
     */
    private function getHandlerStack(): HandlerStack
    {
        return $this->handlerStack;
    }

    private function initializeHttpClient(): void
    {
        $config = $this->getConfig()->requestOptions();

        $config['handler'] = $this->getHandlerStack();

        $config['on_stats'] = function (TransferStats $stats) {
            $this->getDispatcher()->dispatch(TransferStatisticReceived::class, new TransferStatisticReceived($stats));
        };

        $config['on_headers'] = function (ResponseInterface $response) {
            $this->getDispatcher()->dispatch(ResponseHeadersReceived::class, new ResponseHeadersReceived($response));
        };

        $this->httpClient = new GuzzleHttpClient(new \GuzzleHttp\Client($config));
    }

    /**
     * @return HttpClient
     */
    private function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    private function initializeScheduler(): void
    {
        $this->scheduler = new Scheduler(
            $this->getHttpClient(),
            $this->getDispatcher(),
            $this->getHistory(),
            $this->getQueue(),
            $this->getMiddlewareStack(),
            $this->getConfig()->concurrency()
        );
    }

    /**
     * @return Scheduler
     */
    private function getScheduler(): Scheduler
    {
        return $this->scheduler;
    }

    private function initializeSession()
    {
        $this->session = new Session($this->getHttpClient());
    }

    /**
     * @return Session
     */
    private function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @param Extension $extension
     */
    public function addExtension(Extension $extension): void
    {
        $extension->initialize($this->getConfig(), $this->getSession(), $this->getQueue());

        $this->getDispatcher()->addSubscriber($extension);

        $this->extentions[] = $extension;
    }

    private function initializeDefaultExtentions(): void
    {
        $this->addExtension(new AutoThrottle);

        $this->addExtension(new Storage(new StorageService($this->getStorageAdapter())));

        $uriPolicy = new AggregateUriPolicy($this->getConfig()->filterOptions());

        $this->addExtension(new RedirectScheduler($uriPolicy));

        $this->addExtension(new ExtractAndQueueLinks(new LinkExtractor, $uriPolicy, $this->getConfig()->depth()));

        $this->addExtension(new RequestDepth($this->getConfig()->depth()));

        $this->addExtension(new RobotTxt($this->getConfig()->filterOptions()->obeyRobotsTxt()));
    }

    private function initializeEventDispatcher(): void
    {
        $this->dispatcher = new EventDispatcher;
    }

    /**
     * @return EventDispatcher
     */
    private function getDispatcher(): EventDispatcher
    {
        return $this->dispatcher;
    }

    /**
     * Adds a request middleware to the stack
     *
     * @param RequestMiddleware $middleware
     */
    public function addRequestMiddleware(RequestMiddleware $middleware): void
    {
        $this->getMiddlewareStack()->addRequestMiddleware($middleware);
    }

    /**
     * Adds a response middleware to the stack
     *
     * @param ResponseMiddleware $middleware
     */
    public function addResponseMiddleware(ResponseMiddleware $middleware): void
    {
        $this->getMiddlewareStack()->addResponseMiddleware($middleware);
    }

    private function initializeMiddlewareStack(): void
    {
        $this->middlewareStack = new MiddlewareStack;
    }

    /**
     * @return MiddlewareStack
     */
    private function getMiddlewareStack(): MiddlewareStack
    {
        return $this->middlewareStack;
    }

    public function run(): void
    {
        $config = $this->getConfig();

        $this->getDispatcher()->dispatch(BeforeEngineStarted::class, new BeforeEngineStarted);

        $queue = $this->getQueue();

        foreach ($config->startUris() as $uri) {
            $queue->enqueue(new Request('GET', $uri));
        }

        $this->getScheduler()->run();
    }
}
