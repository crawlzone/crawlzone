<?php
declare(strict_types=1);


namespace Crawlzone\Handler;


use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class PuppeteerResponse
{
    private $response;

    private function __construct(\stdClass $response)
    {
        $this->response = $response;
    }

    public static function fromJson(string $json): self
    {
        $response = (new JsonDecode)->decode($json, JsonEncoder::FORMAT);

        return new self($response->response);
    }

    public function getStatus(): int
    {
        return $this->response->status;
    }

    public function getContent(): string
    {
        return $this->response->content;
    }

    public function getHeaders(): array
    {
        return (array) $this->response->headers;
    }

    public function getUrl(): string
    {
        return $this->response->url;
    }
}