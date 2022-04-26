<?php

namespace App\Tests\Functional\UserPaths\Api;

use App\Entity\User;
use App\Security\ApiKeyAuthenticator;
use App\Tests\Functional\TestingTools\UrlInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

Trait ApiConfigurationPathTrait
{
    public function hasNoAccessToConfigurationEndpoint(KernelBrowser $client): void
    {
        $client->request('GET', UrlInterface::CONFIGURATION_ENDPOINT_URL);
        static::assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function hasAccessToConfigurationEndpoint(KernelBrowser $client): void
    {
        $userRepository = static::getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(User::class);

        $client->request(
            'GET',
            UrlInterface::CONFIGURATION_ENDPOINT_URL, 
            [],
            [],
            [
                str_replace('-', '_', 'HTTP_' . ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $userRepository->findAll()[0]->getToken()
            ]
        );
        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
