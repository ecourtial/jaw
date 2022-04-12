<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Configuration;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create sample configuration
        $manager->persist($this->initBlogConfiguration());

        // Create a sample admin user
        $manager->persist($this->createAdminUser());

        // Create a sample regular user
        $manager->persist($this->createRegularUser());

        // Create some categories
        $category1 = $this->createCategory('My first category', 'my-first-category', 'The first one!');
        $manager->persist($category1);
        $category2 = $this->createCategory('Another category', 'another-category', 'Another one!');
        $manager->persist($category2);

        $manager->flush();
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
        $configuration->setGoogleAnalyticsId('1234A');

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

    private function createCategory(string $title, string $slug, string $summary): Category
    {
        $category = new Category();
        $category->setTitle($title);
        $category->setSlug($slug);
        $category->setSummary($summary);

        return $category;
    }
}
