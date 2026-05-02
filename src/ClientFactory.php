<?php

declare(strict_types=1);

namespace MellowApiBundle;

use Mellow\Client;
use Mellow\Store\TokenStoreInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\HttplugClient;

class ClientFactory
{
    public function __construct(
        private readonly string $url,
        private readonly string $username,
        #[\SensitiveParameter]
        private readonly string $password,
        private readonly TokenStoreInterface $tokenStorage,
    ) {
    }

    public function create(?int $companyId = null): Client
    {
        $client = $this->buildClient();

        if (null !== $companyId) {
            $client->setCompany($companyId);
        }

        $token = $this->resolveToken($client);
        $client->authenticate($token);

        return $client;
    }

    private function resolveToken(Client $client): string
    {
        if (null !== $token = $this->tokenStorage->getToken()) {
            return $token;
        }

        if (null !== $refreshToken = $this->tokenStorage->getRefreshToken()) {
            $credentials = $client->login()->refresh($refreshToken);
            $this->tokenStorage->save($credentials->token, $credentials->refreshToken);

            return $credentials->token;
        }

        $credentials = $client->login()
            ->login($this->username, $this->password);
        $this->tokenStorage->save($credentials->token, $credentials->refreshToken);

        return $credentials->token;
    }

    public function buildClient(): Client
    {
        $httpClient = HttpClient::create([
            'max_redirects' => 7,
            'base_uri' => $this->url,
        ]);
        $httplugClient = new HttplugClient($httpClient);

        return Client::createWithHttpClient(
            $httplugClient,
        );
    }
}
