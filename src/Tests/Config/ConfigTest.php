<?php

namespace Crawlzone\Tests\Config;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Crawlzone\Config\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testDefaults()
    {
        $config = Config::fromArray([
            'start_uri' => ['http://test.com'],
        ]);

        $expected = [
            'start_uri' => ['http://test.com'],
            'concurrency' => 10,
            'save_progress_in' => 'memory',
            'request_options' => [
                'verify' => true,
                'cookies' => true,
                'allow_redirects' => false,
                'debug' => false,
            ],
            'filter' => [
                'robotstxt_obey' => false,
                'allow' => [],
                'allow_domains' => [],
                'deny_domains' => [],
                'deny' => []
            ],
            'autothrottle' => [
                'enabled' => true,
                'min_delay' => 0,
                'max_delay' => 60
            ]
        ];

        $this->assertEquals($expected, $config->toArray());
    }

    public function testLoginConfigException()
    {
        $this->expectException(InvalidConfigurationException::class);

        $config = Config::fromArray([
            'start_uri' => ['http://test.com'],
            'login' => []
        ]);
    }

    public function testLoginConfigFormParamsException()
    {
        $this->expectException(InvalidConfigurationException::class);

        $config = Config::fromArray([
            'start_uri' => ['http://test.com'],
            'login' => [
                'login_uri' => 'http://test.com'
            ]
        ]);
    }

    public function testFullConfig()
    {
        $config = Config::fromArray([
            'start_uri' => ['http://test.com'],
            'concurrency' => 10,
            'depth' => 3,
            'save_progress_in' => '/path/to/my/sqlite.db',
            'filter' => [
                'robotstxt_obey' => false,
                'allow' => ['test','test1'],
                'allow_domains' => ['test.com','test1.com'],
                'deny_domains' => ['test2.com','test3.com'],
                'deny' => ['test2','test3'],
            ],
            'request_options' => [
                'verify' => false,
                'cookies' => true,
                'allow_redirects' => false,
                'debug' => true,
                'connect_timeout' => 0,
                'timeout' => 0,
                'read_timeout' => 60,
                'decode_content' => true,
                'force_ip_resolve' => null,
                'proxy' => [
                    'http'  => 'tcp://localhost:8125', // Use this proxy with "http"
                    'https' => 'tcp://localhost:9124', // Use this proxy with "https",
                    'no' => ['.mit.edu', 'foo.com']    // Don't use a proxy with these
                 ],
                'stream' => false,
                'version' => '1.1',
                'cert' => '/path/server.pem',
                'ssl_key' => ['/path/key.pem', 'password']
            ],
            'autothrottle' => [
                'enabled' => true,
                'min_delay' => 0,
                'max_delay' => 60
            ]
        ]);

        $expected = [
            'start_uri' => ['http://test.com'],
            'concurrency' => 10,
            'depth' => 3,
            'save_progress_in' => '/path/to/my/sqlite.db',
            'filter' => [
                'robotstxt_obey' => false,
                'allow' => ['test','test1'],
                'allow_domains' => ['test.com','test1.com'],
                'deny_domains' => ['test2.com','test3.com'],
                'deny' => ['test2','test3'],
            ],
            'request_options' => [
                'verify' => false,
                'cookies' => true,
                'allow_redirects' => false,
                'debug' => true,
                'connect_timeout' => 0,
                'timeout' => 0,
                'read_timeout' => 60,
                'decode_content' => true,
                'force_ip_resolve' => null,
                'proxy' => [
                    'http'  => 'tcp://localhost:8125', // Use this proxy with "http"
                    'https' => 'tcp://localhost:9124', // Use this proxy with "https",
                    'no' => ['.mit.edu', 'foo.com']    // Don't use a proxy with these
                ],
                'stream' => false,
                'version' => '1.1',
                'cert' => '/path/server.pem',
                'ssl_key' => ['/path/key.pem', 'password']
            ],
            'autothrottle' => [
                'enabled' => true,
                'min_delay' => 0,
                'max_delay' => 60
            ],
        ];
        $this->assertEquals($expected, $config->toArray());
    }
}
