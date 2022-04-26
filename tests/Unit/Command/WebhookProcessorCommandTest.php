<?php

namespace App\Tests\Unit\Command;

use App\Command\WebhookProcessorCommand;
use App\Service\WebhookService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class WebhookProcessorCommandTest extends TestCase
{
    public function testFailure(): void
    {
        $webhookService = $this->createMock(WebhookService::class);
        $application = new Application();
        $application->add(new WebhookProcessorCommand($webhookService));
        $commandTester = new CommandTester($application->find('app:webhook:process'));

        $webhookService->method('process')->willThrowException(new \LogicException('AH!'));

        $returnCode = $commandTester->execute([]);

        static::assertEquals(1, $returnCode);
        static::assertStringContainsString('[ERROR] AH!', $commandTester->getDisplay());
    }

    public function testSuccess(): void
    {
        $webhookService = $this->createMock(WebhookService::class);
        $application = new Application();
        $application->add(new WebhookProcessorCommand($webhookService));
        $commandTester = new CommandTester($application->find('app:webhook:process'));

        $webhookService->method('process')->willReturnCallback(function() {
            $result = [
                ['status' => 'success', 'message' => "OK"],
                ['status' => 'error', 'message' => "KO"],
                ['status' => 'foo', 'message' => "bar"],
            ];

            foreach ($result as $item) {
                yield $item;
            }
        });

        $returnCode = $commandTester->execute([]);
        static::assertEquals(0, $returnCode);
        static::assertStringContainsString('[OK] OK', $commandTester->getDisplay());
        static::assertStringContainsString('[ERROR] KO', $commandTester->getDisplay());
        static::assertStringContainsString('! [NOTE] bar', $commandTester->getDisplay());
    }
}
