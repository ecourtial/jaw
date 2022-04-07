<?php

namespace App\DataFixtures;

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
        $configuration = new Configuration();
        $configuration->setBlogTitle('MyBlog');
        $configuration->setBlogDescription('My awesome blog.');
        $configuration->setCopyrightMessage('Do not copy my stuff.');
        $configuration->setCopyrightExtraMessage('Or I will unleash my poodle.');
        $configuration->setLinkedinUsername('LinkedinPseudo');
        $configuration->setGithubUsername('GithubPseudo');
        $configuration->setGoogleAnalyticsId('1234A');

        $manager->persist($configuration);

        // Create a sample admin user
        $user = new User();
        $user->setEmail('someEmail@foo.com');
        $user->setToken('someToken123456');
        $password = $this->passwordHasher->hashPassword($user, 'somePassword123456');
        $user->setPassword($password);
        $user->setUsername('John');
        $user->setFullName('John your neighbor');
        $user->setRoles(['ROLE_ADMIN']);

        $manager->persist($user);

        // Create a sample regular user
        $user = new User();
        $user->setEmail('someEmailRegular@foo.com');
        $user->setToken('someToken123456aa');
        $password = $this->passwordHasher->hashPassword($user, 'somePassword123456aa');
        $user->setPassword($password);
        $user->setUsername('JohntheRegular');
        $user->setFullName('John your regular neighbor');
        $user->setRoles(['ROLE_USER']);

        $manager->persist($user);

        $manager->flush();
    }
}
