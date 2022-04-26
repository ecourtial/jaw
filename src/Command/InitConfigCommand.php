<?php

namespace App\Command;

use App\Entity\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-config',
    description: 'Create an empty configuration'
)]
class InitConfigCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * This method is executed after interact() and initialize(). It usually
     * contains the logic to execute to complete this command task.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->entityManager->persist(
            (new Configuration())
                ->setBlogDescription('A dummy description')
                ->setBlogTitle('A dummy title')
                ->setCopyrightMessage('A dummy copyright message')
                ->setCopyrightExtraMessage('A dummy copyright extra message')
                ->setGithubUsername('A dummy github username')
                ->setGoogleAnalyticsId('A dummy Google Analytics identifier')
                ->setLinkedinUsername('A dummy Linkedin username')
        );

        $this->entityManager->flush();

        $this->io->success('The empty configuration was successfully created');

        return Command::SUCCESS;
    }
}
