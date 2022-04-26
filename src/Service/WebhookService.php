<?php

namespace App\Service;

use App\Entity\Webhook;
use App\Repository\ConfigurationRepository;
use App\Repository\WebhookRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebhookService
{
    private ?string $callbackUrl;
    private int $totalAttemptCount = 0;
    private int $successCount = 0;

    public function __construct(
        private readonly ConfigurationRepository $configurationRepository,
        private readonly WebhookRepository $webhookRepository,
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function process(): \Generator
    {
        $this->callbackUrl = $this->configurationRepository->get()->getCallbackUrl();

        if (null === $this->callbackUrl || trim($this->callbackUrl) === '') {
            throw new \LogicException('The callback URL has not been defined. Aborting.');
        }

        while ($webhook = $this->webhookRepository->getFirstUnprocessed()) {
            try {
                $this->processWebhook($webhook);
                $webhook->setProcessedDate(new \DateTime());
                $result = ['status' => 'success', 'message' => "Webhook with id #{$webhook->getId()} processed with success."];
                $this->successCount++;
            } catch (\Throwable $exception) {
                $this->logger->error("Impossible to process the webhook.", ['exception' => $exception]);

                // We increment it later in this code
                $throttleMessage = '';
                $currentAttemptCount = $webhook->getAttemptCount() + 1;
                if ($currentAttemptCount < Webhook::MAX_ATTEMPT_COUNT) {
                    $throttleMessage = " For the next attempt, we will wait for {$this->getThrottleDuration($currentAttemptCount)} seconds.";
                }

                $result = [
                    'status' => 'error',
                    'message' => "Impossible to process the webhook with id #{$webhook->getId()} "
                        . "(tentative $currentAttemptCount of " . Webhook::MAX_ATTEMPT_COUNT . "). See logs for more details.$throttleMessage"
                ];
            }

            $webhook->incrementAttemptCount();
            $this->webhookRepository->save($webhook);

            $this->totalAttemptCount++;

            yield $result;

            if ($this->totalAttemptCount === 10) {
                break;
            }
        }

        yield ['status' => 'success', 'message' => $this->totalAttemptCount . ' attempts have been made, ' . $this->successCount . ' with success.'];
    }

    private function processWebhook(Webhook $webhook): void
    {
        sleep($this->getThrottleDuration($webhook->getAttemptCount()));

        $response = $this->httpClient->request(
            'POST',
            // @phpstan-ignore-next-line
            $this->callbackUrl,
            [
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
            ],
        );

        if (false === str_starts_with((string)$response->getStatusCode(), '2')) {
            throw new \LogicException('Server returned a' . $response->getStatusCode() . ' status code');
        }
    }

    private function getThrottleDuration(int $attemptCount): int
    {
        return match ($attemptCount) {
            0 => 0,
            1 => 30,
            2 => 60,
            3 => 120,
            4 => 300,
            default => 0,
        };
    }
}
