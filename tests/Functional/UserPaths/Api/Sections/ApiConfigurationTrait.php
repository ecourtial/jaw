<?php

namespace App\Tests\Functional\UserPaths\Api\Sections;

use App\Repository\ConfigurationRepository;
use App\Security\ApiKeyAuthenticator;
use App\Tests\Functional\TestingTools\RequestTools;
use App\Tests\Functional\TestingTools\UrlInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait ApiConfigurationTrait
{
    public function hasNoAccessToConfigurationEndpoint(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::CONFIGURATION_ENDPOINT_URL,
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => 'nope'
            ]
        );

        $client->request('GET', UrlInterface::CONFIGURATION_ENDPOINT_URL);
        static::assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function hasAccessToConfigurationEndpoint(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::CONFIGURATION_ENDPOINT_URL,
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getAdminUserToken()
            ]
        );

        static::assertEquals(200, $client->getResponse()->getStatusCode());

        $configurationRepository = static::getContainer()
            ->get(ConfigurationRepository::class);

        $configuration = $configurationRepository->get();

        $expected = [
            'title' => $configuration->getBlogTitle(),
            'description' => $configuration->getBlogDescription(),
            'callbackUrl' => $configuration->getCallbackUrl(),
            'webhooksEnabled' => $configuration->getWebhooksEnabled(),
            'createdAt' => $configuration->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $configuration->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            'copyrightMessage' => $configuration->getCopyrightMessage(),
            'copyrightExtraMessage' => $configuration->getCopyrightExtraMessage(),
            'githubUserName' => $configuration->getGithubUsername(),
            'linkedinUserName' => $configuration->getLinkedinUsername(),
            'googleAnalyticsId' => $configuration->getGoogleAnalyticsId(),
        ];

        static::assertEquals($expected, \json_decode($client->getResponse()->getContent(), true));
    }
}
