<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Configuration;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // BE CAREFUL IF CHANGING SOMETHING EXISTING HERE, AS DATA ARE USED FOR FUNCTIONAL TESTS.

        // Create sample configuration
        $manager->persist($this->initBlogConfiguration());

        // Create a sample admin user
        $manager->persist($this->createAdminUser());

        // Create a sample regular user (keep in this order, as the user id is used for some tests)
        $regularUser = $this->createRegularUser();
        $manager->persist($regularUser);
        $manager->flush();

        // Create some categories containing a few posts
        $categories = $this->getFixturesCategories($regularUser);
        $manager->persist($categories[0]);
        $manager->persist($categories[1]);

        $manager->flush();
    }

    /** @return Category[] */
    private function getFixturesCategories(?User $regularUser = null): array
    {
        $regularUser = $regularUser ?? (new User())->setId(2);

        return [
            (new Category())
                ->setTitle('My first category')
                ->setSlug('my-first-category')
                ->setSummary('The first one!')
                ->addPost(
                    (new Post())
                    ->setTitle('My first post')
                    ->setSlug('my-first-post')
                    ->setSummary('The summary 1 keyword')
                    ->setContent('Then content 1')
                    ->setAuthor($regularUser)
                    ->setLanguage('en')
                    ->setTopPost(false)
                    ->setOnline(true)
                )
            ,
            (new Category())
                ->setTitle('Another category')
                ->setSlug('another-category')
                ->setSummary('Another one!')
                ->addPost(
                    (new Post())
                        ->setTitle('My second post')
                        ->setSlug('my-second-post')
                        ->setSummary('The summary 2')
                        ->setContent('Then content 2')
                        ->setAuthor($regularUser)
                        ->setLanguage('en')
                        ->setTopPost(true)
                        ->setOnline(true)
                )
                ->addPost(
                    (new Post())
                        ->setTitle('My third post keyword')
                        ->setSlug('my-third-post')
                        ->setSummary('The summary 3')
                        ->setContent('Then content 3')
                        ->setAuthor($regularUser)
                        ->setLanguage('en')
                        ->setTopPost(true)
                        ->setOnline(false)
                        ->setObsolete(true)
                )
        ];
    }

    private function initBlogConfiguration(): Configuration
    {
        $configuration = new Configuration();
        $configuration->setBlogTitle('MyBlog');
        $configuration->setBlogDescription('My awesome blog.');
        $configuration->setCopyrightMessage('Do not copy my stuff.');
        $configuration->setCopyrightExtraMessage('Or I will unleash my poodle.');
        $configuration->setLinkedinUsername('LinkedinPseudo');
        $configuration->setGithubUsername('GithubPseudo');
        $configuration->setGoogleAnalyticsId('1234Abcdef');

        return $configuration;
    }

    private function createAdminUser(): User
    {
        $user = new User();
        $user->setEmail('someEmail@foo.com');
        $user->setToken('someToken123456');
        $password = $this->passwordHasher->hashPassword($user, 'somePassword123456');
        $user->setPassword($password);
        $user->setUsername('John');
        $user->setFullName('John your neighbor');
        $user->setRoles(['ROLE_ADMIN']);

        return $user;
    }

    private function createRegularUser(): User
    {
        $user = new User();
        $user->setEmail('someEmailRegular@foo.com');
        $user->setToken('someToken123456aa');
        $password = $this->passwordHasher->hashPassword($user, 'somePassword123456aa');
        $user->setPassword($password);
        $user->setUsername('JohntheRegular');
        $user->setFullName('John your regular neighbor');
        $user->setRoles(['ROLE_USER']);

        return $user;
    }
}
