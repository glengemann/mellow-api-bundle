<?php

declare(strict_types=1);

namespace MellowApiBundle\Login;

use Mellow\Api\Login\Response\CredentialResponse;
use Mellow\Api\Login\Response\LoginResponse;
use Mellow\LoginInterface;
use Mellow\Store\TokenStoreInterface;
use MellowApiBundle\ClientFactory;

class LoginService implements LoginInterface
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly TokenStoreInterface $tokenStore,
    ) {
    }

    public function login(?int $twoFactorCode = null): LoginResponse
    {
        $client = $this->clientFactory->buildClient();

        /** @var LoginResponse $response */
        $response = $client->login()->login(
            $this->clientFactory->getUsername(),
            $this->clientFactory->getPassword(),
            $twoFactorCode ?? 0,
        );

        if (false === $response->requiresTwoFactor()) {
            $this->tokenStore->save($response->token, $response->refreshToken);
        }

        return $response;
    }
}
