[![Build Status](https://travis-ci.org/crawlzone/crawlzone.svg?branch=master)](https://travis-ci.org/crawlzone/crawlzone)
[![Coverage Status](https://coveralls.io/repos/github/crawlzone/crawlzone/badge.svg?branch=master)](https://coveralls.io/github/crawlzone/crawlzone?branch=master)

# Overview

Crawlzone is a fast asynchronous internet crawling framework aiming to provide open source web scraping and testing solution. It can be used for a wide range of purposes, from extracting and indexing structured data to monitoring and automated testing. Available for PHP 7.3, 7.4, 8.0.

## Installation

`composer require crawlzone/crawlzone`

## Key Features

- Asynchronous crawling with customizable concurrency.
- Automatically throttling crawling speed based on the load of the website you are crawling
- If configured, automatically filters out requests forbidden by the `robots.txt` exclusion standard.
- Straightforward middleware system allows you to append headers, extract data, filter or plug any custom functionality to process the request and response.
- Rich filtering capabilities.
- Ability to set crawling depth
- Easy to extend the core by hooking into the crawling process using events.
- Shut down crawler any time and start over without losing the progress.

## Architecture

![Architecture](https://github.com/crawlzone/crawlzone/blob/master/resources/Web%20Crawler%20Architecture.svg)

Here is what's happening for a single request when you run the client:

1. The client queues the initial request (start_uri).
2. The engine looks at the queue and checks if there are any requests.
3. The engine gets the request from the queue and emits the `BeforeRequestSent` event. If the depth option is set in the config, then the `RequestDepth` extension validates the depth of the request. If the obey robots.txt option is set in the config, then the `RobotTxt` extension checks if the request complies with the rules. In a case when the request doesn't comply, the engine emits the `RequestFailed` event and gets the next request from the queue.
4. The engine uses the request middleware stack to pass the request through it.
5. The engine sends an asynchronous request using Guzzle HTTP Client
6. The engine emits the `AfterRequestSent` event and stores the request in the history to avoid crawling the same request again.
7. When response headers are received, but the body has not yet begun to download, the engine emits the `ResponseHeadersReceived` event.
8. The engine emits the `TransferStatisticReceived` event. If the autothrottle option is set in the config, then the `AutoThrottle` extension is executed.
9. The engine uses the response middleware stack to pass the response through it.
10. The engine emits the `ResponseReceived` event. Additionally, if the request status code is greater than or equal to 400, the engine emits `RequestFailed` event.
11. The `ResponseReceived` triggers the `ExtractAndQueueLinks` extension, which extracts and queues the links. The process starts over until the queue is empty.


## Quick Start
```php
<?php

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Crawlzone\Middleware\BaseMiddleware;
use Crawlzone\Client;
use Crawlzone\Middleware\ResponseMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

$config = [
    'start_uri' => ['https://httpbin.org/'],
    'concurrency' => 3,
    'filter' => [
        //A list of string containing domains which will be considered for extracting the links.
        'allow_domains' => ['httpbin.org'],
        //A list of regular expressions that the urls must match in order to be extracted.
        'allow' => ['/get','/ip','/anything']
    ]
];

$client = new Client($config);

$client->addResponseMiddleware(
    new class implements ResponseMiddleware {
        public function processResponse(ResponseInterface $response, RequestInterface $request): ResponseInterface
        {
            printf("Process Response: %s %s \n", $request->getUri(), $response->getStatusCode());

            return $response;
        }
    }
);

$client->run();
```

## Middlewares

Middleware can be written to perform a variety of tasks including authentication, filtering, headers, logging, etc.
To create middleware simply implement `Crawlzone\Middleware\RequestMiddleware` or `Crawlzone\Middleware\ResponseMiddleware` and
then add it to a client:


```php
...

$config = [
    'start_uri' => ['https://httpbin.org/ip']
];

$client = new Client($config);

$client->addRequestMiddleware(
    new class implements RequestMiddleware {
        public function processRequest(RequestInterface $request): RequestInterface
        {
            printf("Middleware 1 Request: %s \n", $request->getUri());
            return $request;
        }
    }
);

$client->addResponseMiddleware(
    new class implements ResponseMiddleware {
        public function processResponse(ResponseInterface $response, RequestInterface $request): ResponseInterface
        {
            printf("Middleware 2 Response: %s %s \n", $request->getUri(), $response->getStatusCode());
            return $response;
        }
    }
);

$client->run();

/*
Output:
Middleware 1 Request: https://httpbin.org/ip
Middleware 2 Response: https://httpbin.org/ip 200
*/

```

To skip the request and go to the next middleware you can `throw new \Crawlzone\Exception\InvalidRequestException` from any middleware. 
The scheduler will catch the exception, notify all subscribers, and ignore the request.  

## Processing server errors

You can use middlewares to handle 4xx or 5xx responses.

```php
...
$config = [
    'start_uri' => ['https://httpbin.org/status/500','https://httpbin.org/status/404'],
    'concurrency' => 1,
];

$client = new Client($config);

$client->addResponseMiddleware(
    new class implements ResponseMiddleware {
        public function processResponse(ResponseInterface $response, RequestInterface $request): ResponseInterface
        {
            printf("Process Failure: %s %s \n", $request->getUri(), $response->getStatusCode());

            return $response;
        }
    }
);

$client->run();
```

## Filtering

Use regular expression to allow or deny specific links. You can also pass array of allowed or denied domains as well. 
Use `robotstxt_obey` option to enable filtering. out requests forbidden by the `robots.txt` exclusion standard

```php

...
$config = [
    'start_uri' => ['http://site.local/'],
    'concurrency' => 1,
    'filter' => [
        'robotstxt_obey' => true,
        'allow' => ['/page\d+','/otherpage'],
        'deny' => ['/logout']
        'allow_domains' => ['site.local'],
        'deny_domains' => ['othersite.local'],
    ]
];
$client = new Client($config);

```

## Autothrottle

Autothrottle is enabled by default (use `autothrottle.enabled => false` to disable). It automatically adjusts scheduler to the optimum crawling speed trying to be nicer to the sites.


**Throttling algorithm**

AutoThrottle algorithm adjusts download delays based on the following rules:

1. When a response is received, the target download delay is calculated as `latency / N` where latency is a latency of the response, and `N` is concurrency.
3. Delay for next requests is set to the average of previous delay and the current delay;
4. Latencies of non-200 responses are not allowed to decrease the delay;
5. Delay can’t become less than `min_delay` or greater than `max_delay`


```php

...
$config = [
    'start_uri' => ['http://site.local/'],
    'concurrency' => 3,
    'autothrottle' => [
        'enabled' => true,
        'min_delay' => 0, // Sets minimum delay between the requests (default 0).
        'max_delay' => 60, // Sets maximun delay between the requests (default 60).
    ]
];

$client = new Client($config);
...

```

## Extension

Basically speaking, extensions are nothing more than event listeners based on the Symfony Event Dispatcher component.
To create extension simply extend `Crawlzone\Extension\Extension` and add it to a client. All extensions have access to a 
`Crawlzone\Config\Config` and `Crawlzone\Session` object, which holds `GuzzleHttp\Client`. This might be helpful if you want to 
make some additional requests or reuse cookie headers for authentication.

```php
...

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Crawlzone\Client;
use Crawlzone\Event\BeforeEngineStarted;
use Crawlzone\Extension\Extension;
use Crawlzone\Middleware\ResponseMiddleware;

$config = [
    'start_uri' => ['http://site.local/admin/']
];

$client = new Client($config);

$loginUri = 'http://site.local/admin/';
$username = 'test';
$password = 'password';

$client->addExtension(new class($loginUri, $username, $password) extends Extension {
    private $loginUri;
    private $username;
    private $password;

    public function __construct(string $loginUri, string $username, string $password)
    {
        $this->loginUri = $loginUri;
        $this->username = $username;
        $this->password = $password;
    }

    public function authenticate(BeforeEngineStarted $event): void
    {
        $this->login($this->loginUri, $this->username, $this->password);
    }

    private function login(string $loginUri, string $username, string $password)
    {
        $formParams = ['username' => $username, 'password' => $password];
        $body = http_build_query($formParams, '', '&');
        $request = new Request('POST', $loginUri, ['content-type' => 'application/x-www-form-urlencoded'], $body);
        $this->getSession()->getHttpClient()->sendAsync($request)->wait();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEngineStarted::class => 'authenticate'
        ];
    }
});

$client->run();

```

**List of supported events `Crawlzone\Event`:**

| Event                     | When?                                         |
| ------------------------- | --------------------------------------------- |
| BeforeEngineStarted       | Right before the engine starts crawling       |
| BeforeRequestSent         | Before the request is scheduled to be sent    |
| AfterRequestSent          | After the request is scheduled                |
| TransferStatisticReceived | When a handler has finished sending a request. Allows you to get access to transfer statistics of a request and access the lower level transfer details. |
| ResponseHeadersReceived   | When the HTTP headers of the response have been received but the body has not yet begun to download. Useful if you want to reject responses that are greater than certain size for example. |
| RequestFailed             | When the request is failed or the exception `InvalidRequestException` has been  thrown from the middleware. |
| ResponseReceived          | When the response is received                 |
| AfterEngineStopped        | After engine stopped crawling                 |


## Command Line Tool

You can use simple command line tool to crawl your site quickly.
First create configuration file:

```bash
./crawler init 

```

Then configure `crawler.yml` and run the crawler with a command:

```bash
./crawler start --config=./crawler.yml 

```
To get more details about request and response use `-vvv` option:

```bash
./crawler start --config=./crawler.yml -vvv 

```

## Configuration

```php

$fullConfig = [
    // A list of URIs to crawl. Required parameter. 
    'start_uri' => ['http://test.com', 'http://test1.com'],
    
    // The number of concurrent requests. Defaut is 10.
    'concurrency' => 10,
    
    // The maximum depth that will be allowed to crawl (Mininum 1, unlimited if not set). 
    'depth' => 1,
    
    // The path to local file where the progress will be stored. Use "memory" to store the progress in memory (default behavior).
    // The crawler uses Sqlite database to store the progress.
    'save_progress_in' => '/path/to/my/sqlite.db',
    
    'filter' => [
        // If enabled, crawler will respect robots.txt policies. Default is false
        'robotstxt_obey' => false,
        
        // A list of regular expressions that the urls must match in order to be extracted. If not given (or empty), it will match all links..
        'allow' => ['test','test1'],
        
        // A list of string containing domains which will be considered for extracting the links.
        'allow_domains' => ['test.com','test1.com'],
        
        // A list of strings containing domains which won’t be considered for extracting the links. It has precedence over the allow_domains parameter.
        'deny_domains' => ['test2.com','test3.com'],
        
        // A list of regular expressions) that the urls must match in order to be excluded (ie. not extracted). It has precedence over the allow parameter.
        'deny' => ['test2','test3'],
    ],
    // Crawler uses Guzzle HTTP Client so most of the Guzzle request options supported
    // For more info go to http://docs.guzzlephp.org/en/stable/request-options.html
    'request_options' => [
        // Describes the SSL certificate verification behavior of a request.
        'verify' => false,
        
        // Specifies whether or not cookies are used in a request or what cookie jar to use or what cookies to send.
        'cookies' => CookieJar::fromArray(['name' => 'test', 'value' => 'test-value'],'localhost'),
        
        // Describes the redirect behavior of a request.
        'allow_redirects' => false,
        
        // Set to true or to enable debug output with the handler used to send a request.
        'debug' => true,
        
        // Float describing the number of seconds to wait while trying to connect to a server. Use 0 to wait indefinitely (the default behavior).
        'connect_timeout' => 0,
        
        // Float describing the timeout of the request in seconds. Use 0 to wait indefinitely (the default behavior).
        'timeout' => 0,
        
        // Float describing the timeout to use when reading a streamed body. Defaults to the value of the default_socket_timeout PHP ini setting
        'read_timeout' => 60,
        
        // Specify whether or not Content-Encoding responses (gzip, deflate, etc.) are automatically decoded.
        'decode_content' => true,
        
        // Set to "v4" if you want the HTTP handlers to use only ipv4 protocol or "v6" for ipv6 protocol.
        'force_ip_resolve' => null,
        
        // Pass an array to specify different proxies for different protocols.
        'proxy' => [
            'http'  => 'tcp://localhost:8125', // Use this proxy with "http"
            'https' => 'tcp://localhost:9124', // Use this proxy with "https",
            'no' => ['.mit.edu', 'foo.com']    // Don't use a proxy with these
         ],
         
         // Set to true to stream a response rather than download it all up-front.
        'stream' => false,
        
        // Protocol version to use with the request.
        'version' => '1.1',
        
        // Set to a string or an array to specify the path to a file containing a PEM formatted client side certificate and password.
        'cert' => '/path/server.pem',
        
        // Specify the path to a file containing a private SSL key in PEM format.
        'ssl_key' => ['/path/key.pem', 'password']
    ],
    
    'autothrottle' => [
        // Enables autothrottle extension. Default is true.
        'enabled' => true,
        
        // Sets minimum delay between the requests.
        'min_delay' => 0,
        
        // Sets maximun delay between the requests.
        'max_delay' => 60
    ]
];

```
## Migration 

The repo was migrated from https://github.com/zstate/crawler


## Thanks for Inspiration

https://scrapy.org/

http://docs.guzzlephp.org/

If you feel that this project is helpful, please give it a star or leave some feedback. This will help me understand the needs and provide future library updates.



