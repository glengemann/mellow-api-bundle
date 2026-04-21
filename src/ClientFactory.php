<?php

declare(strict_types=1);

namespace MellowApiBundle;

use Mellow\Client;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\HttplugClient;

class ClientFactory
{
    public function __construct(
        private readonly string $url,
    ) {
    }

    public function create(): Client
    {
        $httpClient = HttpClient::create([
            'max_redirects' => 7,
            'base_uri' => $this->url,
        ]);
        $httplugClient = new HttplugClient($httpClient);

        return Client::createWithHttpClient($httplugClient);
    }
}
