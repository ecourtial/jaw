<?php

namespace App\Tests\Unit\Service;

use App\Entity\Configuration;
use App\Entity\Webhook;
use App\Repository\ConfigurationRepository;
use App\Repository\WebhookRepository;
use App\Service\WebhookService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WebhookServiceTest extends TestCase
{
    private ConfigurationRepository $configurationRepository;
    private WebhookRepository $webhookRepository;
    private LoggerInterface $logger;
    private HttpClientInterface $httpClient;
    private WebhookService $service;

    public function setUp(): void
    {
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->webhookRepository = $this->createMock(WebhookRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->service = new WebhookService($this->configurationRepository, $this->webhookRepository, $this->logger, $this->httpClient);
    }

    public function testWebhooksAreDisabled(): void
    {
        $configuration = (new Configuration())->setCallbackUrl('http://foo.bar')->setWebhooksEnabled(false);
        $this->configurationRepository->method('get')->willReturn($configuration);

        static::expectExceptionMessage('Webhooks are disabled. Aborting.');

        foreach ($this->service->process() as $processResult) {

        }
    }

    public function testMissingCallback(): void
    {
        $configuration = (new Configuration())->setCallbackUrl(' ')->setWebhooksEnabled(true);
        $this->configurationRepository->method('get')->willReturn($configuration);

        static::expectExceptionMessage('The callback URL has not been defined. Aborting.');

        foreach ($this->service->process() as $processResult) {

        }
    }

    public function testOneCaseWithSuccess(): void
    {
        $configuration = (new Configuration())->setCallbackUrl('http://foo.bar')->setWebhooksEnabled(true);
        $this->configurationRepository->method('get')->willReturn($configuration);

        $webhook = (new Webhook())->setResourceType('post')->setId(2)->setAction(Webhook::RESOURCE_ACTION_EDITION);

        $this->webhookRepository->method('getFirstUnprocessed')
            ->willReturnOnConsecutiveCalls($webhook, null);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->httpClient->expects(static::once())->method('request')->with(
            'POST',
            $configuration->getCallbackUrl(),
            $this->getRequestContent($webhook)
        )->willReturn($response);

        $result = [];

        foreach ($this->service->process() as $processResult) {
            $result[] = $processResult;
        }

        static::assertEquals(
            [
                ['status' => 'success', 'message' => 'Webhook with id #2 processed with success.'],
                ['status' => 'success', 'message' => '1 attempts have been made, 1 with success.'],
            ],
            $result
        );
    }

    public function testOneCaseWithError(): void
    {
        $configuration = (new Configuration())->setCallbackUrl('http://foo.bar')->setWebhooksEnabled(true);
        $this->configurationRepository->method('get')->willReturn($configuration);

        $webhook = (new Webhook())->setResourceType('post')->setId(2)->setAction(Webhook::RESOURCE_ACTION_EDITION);

        $this->webhookRepository->method('getFirstUnprocessed')
            ->willReturnOnConsecutiveCalls($webhook, null);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);

        $this->httpClient->expects(static::once())->method('request')->with(
            'POST',
            $configuration->getCallbackUrl(),
            $this->getRequestContent($webhook)
        )->willReturn($response);

        $result = [];

        foreach ($this->service->process() as $processResult) {
            $result[] = $processResult;
        }

        static::assertEquals(
            [
                ['status' => 'error', 'message' => 'Impossible to process the webhook with id #2 (tentative 1 of 5). See logs for more details. For the next attempt, we will wait for 30 seconds.'],
                ['status' => 'success', 'message' => '1 attempts have been made, 0 with success.'],
            ],
            $result
        );
    }

    private function getRequestContent(Webhook $webhook): array
    {
        return [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8',
                'User-Agent' => 'JAW Webhook processor',
            ],
            'body' => [
                'resourceType' => $webhook->getResourceType(),
                'resourceId' => $webhook->getResourceId(),
                'actionType' => $webhook->getAction(),
                'currentAttemptCount' => $webhook->getAttemptCount() + 1,
                'lastAttemptDate' => $webhook->getLastAttempt()
            ]
        ];
    }
}
