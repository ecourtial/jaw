<?php

namespace App\Tests\Functional\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private static UserRepository $userRepository;

    public static function setUpBeforeClass(): void
    {
        $kernel = self::bootKernel();

        static::$userRepository = $kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(User::class);
    }

    public function testSave(): void
    {
        $user = (new User())
            ->setUsername('Toto')
            ->setEmail('toto@toto.fr')
            ->setFullName('Toto the toto')
            ->setToken('token')
            ->setRoles([])
            ->setPassword('password');

        static::$userRepository->save($user);
        $saveUser = static::$userRepository->find($user->getId());
        static::assertEquals('Toto', $saveUser->getUsername());
    }

    public function testFindByUserName(): void
    {
        // User exists
        $user = static::$userRepository->findByUsername('John');
        static::assertEquals('John your neighbor', $user->getFullName());

        // User does not exist
        static::expectException(NoResultException::class);
        static::$userRepository->findByUsername('Bar');
    }
}
