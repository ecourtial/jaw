<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Validator\UserValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:migrate-db',
    description: 'Migrate the old db'
)]
class MigrationCommand extends Command
{
    private SymfonyStyle $io;
    private EntityManagerInterface $entityManager;
    private CategoryRepository $categoryRepository;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository,
        UserRepository $userRepository,
        string $name = null
    ) {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->categoryRepository = $categoryRepository;
        $this->userRepository = $userRepository;
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
        $migratedPostsCount = 0;

        $user = $this->userRepository->find(1);
        $categ = $this->categoryRepository->find(1);

        // ATTENTION AU LANGUAGE JAI # OU QUATRE ARTICLES EN ANGLAIS

        foreach ($this->getData() as $entry) {
            $post = (new Post())
                ->setLanguage($entry['language'])
                ->setSlug($entry['slug'])
                ->setSummary($entry['summary'])
                ->setTitle($entry['title'])
                ->setContent($entry['content'])
                ->setPublishedAt($entry['date'])
                ->setTopPost($entry['toppost'])
                ->setOnline($entry['online'])
                ->setAuthor($user)
                ->setCategory($categ);

            $this->entityManager->persist($post);
            $migratedPostsCount++;

            if ($migratedPostsCount % 2 === 0) {
                $this->entityManager->flush();
                gc_collect_cycles();
            }
        }

        $this->entityManager->flush();

        $this->io->success(sprintf('%s were successfully migrated', $migratedPostsCount));

        return Command::SUCCESS;
    }

    private function getData(): \Generator
    {
        $data = [
            [
                'title' => 'One title',
                'summary' => 'One summary',
                'content' => 'One content',
                'date' => new \DateTime(),
                'language' => 'fr',
                'toppost' => true,
                'online' => true,
                'slug' => 'one-slug'
            ],
            [
                'title' => '2 One title',
                'summary' => '2 One summary',
                'content' => '2 One content',
                'date' => new \DateTime(),
                'language' => 'fr',
                'toppost' => true,
                'online' => true,
                'slug' => '-one-slug'
            ]
        ];

        foreach ($data as $entry) {
            yield $entry;
        }
    }

    /**
     * The command help is usually included in the configure() method, but when
     * it's too long, it's better to define a separate method to maintain the
     * code readability.
     */
    private function getCommandHelp(): string
    {
        return <<<'HELP'
            The <info>%command.name%</info> command creates new users and saves them in the database:

              <info>php %command.full_name%</info> <comment>username password email</comment>
              <comment>Ex: bin/console app:add-user pseudo password my@email.com "John Smith" --admin</comment>

            By default the command creates regular users. To create administrator users,
            add the <comment>--admin</comment> option like in the example above.
            HELP;
    }
}
