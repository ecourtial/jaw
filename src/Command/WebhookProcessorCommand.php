<?php

namespace App\Command;

use App\Service\WebhookService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:webhook:process',
    description: 'Processes the pending webhooks'
)]
class WebhookProcessorCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly WebhookService $webhookService,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            foreach ($this->webhookService->process() as $result) {
                if ($result['status'] === 'success') {
                    $this->io->success($result['message']);
                } elseif ($result['status'] === 'error') {
                    $this->io->error($result['message']);
                } else {
                    $this->io->note($result['message']);
                }
            }
        } catch (\Throwable $exception) {
            $this->io->error($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
