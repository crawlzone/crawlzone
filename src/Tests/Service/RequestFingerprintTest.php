<?php

namespace Crawlzone\Tests\Service;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Crawlzone\Service\RequestFingerprint;

class RequestFingerprintTest extends TestCase
{
    public function testUrlQueryOrder()
    {
        $requestFingerPrint1 = RequestFingerprint::calculate(
            new Request('GET', 'http://www.example.com/test?id=1&user=2')
        );

        $requestFingerPrint2 = RequestFingerprint::calculate(
            new Request('GET', 'http://www.example.com/test?user=2&id=1')
        );

        $this->assertEquals($requestFingerPrint1, $requestFingerPrint2);
    }

    public function testEmptyPath()
    {
        $requestFingerPrint1 = RequestFingerprint::calculate(
            new Request('GET', 'http://www.example.com/')
        );

        $requestFingerPrint2 = RequestFingerprint::calculate(
            new Request('GET', 'http://www.example.com')
        );

        $this->assertEquals($requestFingerPrint1, $requestFingerPrint2);
    }

    public function testSegment()
    {
        $requestFingerPrint1 = RequestFingerprint::calculate(
            new Request('GET', 'http://www.example.com/#path')
        );

        $requestFingerPrint2 = RequestFingerprint::calculate(
            new Request('GET', 'http://www.example.com/')
        );

        $this->assertNotEquals($requestFingerPrint1, $requestFingerPrint2);
    }

    public function testMehtod()
    {
        $requestFingerPrint1 = RequestFingerprint::calculate(
            new Request('POST', 'http://www.example.com/')
        );

        $requestFingerPrint2 = RequestFingerprint::calculate(
            new Request('GET', 'http://www.example.com/')
        );

        $this->assertNotEquals($requestFingerPrint1, $requestFingerPrint2);
    }

    public function testBody()
    {
        $requestFingerPrint1 = RequestFingerprint::calculate(
            new Request('POST', 'http://www.example.com/', [], 'id=1&user=2')
        );

        $requestFingerPrint2 = RequestFingerprint::calculate(
            new Request('POST', 'http://www.example.com/', [])
        );

        $this->assertNotEquals($requestFingerPrint1, $requestFingerPrint2);
    }

    public function testBodyOrder()
    {
        $requestFingerPrint1 = RequestFingerprint::calculate(
            new Request('POST', 'http://www.example.com/', [], 'id=1&user=2')
        );

        $requestFingerPrint2 = RequestFingerprint::calculate(
            new Request('POST', 'http://www.example.com/', [], 'user=2&id=1')
        );

        $this->assertNotEquals($requestFingerPrint1, $requestFingerPrint2);
    }

    public function testIgnoreHeaders()
    {
        $requestFingerPrint1 = RequestFingerprint::calculate(
            new Request('POST', 'http://www.example.com/', ['Accept-Language' => ['en-US', 'en;q=0.6']], 'id=1&user=2')
        );

        $requestFingerPrint2 = RequestFingerprint::calculate(
            new Request('POST', 'http://www.example.com/', ['Cookie' => ['ss=123456']], 'id=1&user=2')
        );

        $this->assertEquals($requestFingerPrint1, $requestFingerPrint2);
    }

    public function testCookieHeader()
    {
        $requestFingerPrint1 = RequestFingerprint::calculate(
            new Request('POST', 'http://www.example.com/', ['Cookie' => ['ss=12345']], 'id=1&user=2'),
            ['Cookie']
        );

        $requestFingerPrint2 = RequestFingerprint::calculate(
            new Request('POST', 'http://www.example.com/', ['Cookie' => ['ss=123456789']], 'id=1&user=2'),
            ['Cookie']
        );

        $this->assertNotEquals($requestFingerPrint1, $requestFingerPrint2);
    }

    public function testSameHeaders()
    {
        $requestFingerPrint1 = RequestFingerprint::calculate(
            new Request('POST', 'http://www.example.com/', ['Cookie' => ['ss=12345'], 'Accept-Encoding' => ['gzip', 'deflate', 'br']], 'id=1&user=2'),
            ['Cookie', 'Accept-Encoding']
        );

        $requestFingerPrint2 = RequestFingerprint::calculate(
            new Request('POST', 'http://www.example.com/', ['Accept-Encoding' => ['deflate', 'br', 'gzip'], 'Cookie' => ['ss=12345']], 'id=1&user=2'),
            ['Cookie', 'Accept-Encoding']
        );

        $this->assertEquals($requestFingerPrint1, $requestFingerPrint2);
    }
}
