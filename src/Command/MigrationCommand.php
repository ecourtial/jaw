<?php

/** THIS IS AN EXAMPLE OF A CUSTOM MIGRATION FROM AN OLD BLOG TO JAW */

namespace App\Command;

use App\Entity\Category;
use App\Entity\Post;
use App\Repository\UserRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-db',
    description: 'Migrate the old db'
)]
class MigrationCommand extends Command
{
    private SymfonyStyle $io;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private Connection $connection;
    /** @var string[]  */
    private array $charMap;
    /** @var \App\Entity\Category[] */
    private array $categories = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        string $name = null
    ) {
        parent::__construct($name);

        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;

        $this->connection = new Connection(
            [
                'user' => 'root',
                'password' => 'root',
                'dbname' => 'dynamic',
                'host' => 'mysql',
                //'port' => $port
            ],
            new Driver(),
        );

        $this->charMap = array_flip([
            'à' => 'Ã',
            'â' => 'Ã¢',
            'é' => 'Ã©',
            'è' => 'Ã¨',
            'ê' => 'Ãª',
            'ë' => 'Ã«',
            'î' => 'Ã®',
            'ï' => 'Ã¯',
            'ô' => 'Ã´',
            'ö' => 'Ã¶',
            'ù' => 'Ã¹',
            'û' => 'Ã»',
            'ü' => 'Ã¼',
            'ç' => 'Ã§',
            'œ' => 'Å',
            '€' => 'â',
            '°' => 'Â°',
            // 'À' => 'Ã',
            // 'Â' => 'Ã',
            // 'É' => 'Ã',
            // 'È' => 'Ã',
            // 'Ê' => 'Ã',
            // 'Ë' => 'Ã',
            // 'Î' => 'Ã',
            // 'Ï' => 'Ã',
            // 'Ô' => 'Ã',
            // 'Ö' => 'Ã',
            // 'Ù' => 'Ã',
            // 'Û' => 'Ã',
            // 'Ü' => 'Ã',
            // 'Ç' => 'Ã',
            // 'Œ' => 'Å'
        ]);
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
        $this->migrateCategories();
        $this->migratePosts();

        return Command::SUCCESS;
    }

    private function migrateCategories(): void
    {
        $migratedCategoriesCount = 0;

        $readStatment = $this->connection->prepare('SELECT * FROM categories');
        $result = $readStatment->executeQuery();

        while ($row = $result->fetchAssociative()) {
            $category = (new Category())
                ->setSlug($row['URL'])
                ->setTitle($this->cleanAccents($row['NAME']))
                ->setSummary($this->cleanAccents($row['DESCRIPTION']));

            $this->entityManager->persist($category);
            $this->categories[$row['ID']] = $category;
            $migratedCategoriesCount++;
        }

        $this->entityManager->flush();

        $this->io->success(sprintf('%s categories were successfully migrated', $migratedCategoriesCount));
    }

    private function cleanAccents(string $stringToClean): string
    {
        $data = strtr($stringToClean, $this->charMap);

        return  str_replace('€€™', "'", $data);
    }

    private function migratePosts(): void
    {
        $migratedPostsCount = 0;
        $user = $this->userRepository->find(1);
        $postsInEnglish = [278, 279, 284, 286, 289, 290];

        foreach ($this->getPosts() as $entry) {
            $language = in_array($entry['ID'], $postsInEnglish) ? 'en' : 'fr';

            $post = (new Post())
                ->setLanguage($language)
                ->setSlug($entry['URL'])
                ->setSummary($this->cleanAccents($entry['DESCRIPTION']))
                ->setTitle($this->cleanAccents($entry['TITLE']))
                ->setContent($this->cleanAccents($entry['CONTENT']))
                ->setPublishedAt(new \DateTime($entry['DATE']))
                ->setTopPost($entry['HOME'])
                ->setOnline($entry['Online'])
                ->setObsolete($entry['Obsolete'])
                ->setAuthor($user)
                ->setCategory($this->categories[$entry['CATEG']]);

            $this->entityManager->persist($post);
            $migratedPostsCount++;

            if ($migratedPostsCount === 10) {
                $this->entityManager->flush();
                gc_collect_cycles();
            }
        }

        $this->entityManager->flush();

        $this->io->success(sprintf('%s posts were successfully migrated', $migratedPostsCount));
    }

    private function getPosts(): \Generator
    {
        $readStatment = $this->connection->prepare('SELECT * FROM articles');
        $result = $readStatment->executeQuery();

        while ($row = $result->fetchAssociative()) {
            yield $row;
        }
    }
}
