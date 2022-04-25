<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PostRepositoryTest extends KernelTestCase
{
    private CategoryRepository $categoryRepository;
    private PostRepository $postRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function setup(): void
    {
        $kernel = self::bootKernel();

        $this->categoryRepository = $kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Category::class);

        $this->postRepository = $kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Post::class);

        $this->userRepository = $kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(User::class);

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }


    public function testCreateAndDelete(): void
    {
        // Creation

        $post = (new Post())
            ->setTitle('The Title')
            ->setSummary('The Summary')
            ->setSlug('the-slug')
            ->setObsolete(false)
            ->setPublishedAt(new \DateTime())
            ->setOnline(true)
            ->setTopPost(true)
            ->setContent('The content')
            ->setLanguage('fr')
            ->setAuthor($this->userRepository->find(1))
            ->setCategory($this->categoryRepository->find(1));

        $this->postRepository->save($post);
        $this->entityManager->flush();

        static::assertTrue(is_int($post->getId()));

        // Delete

        $this->postRepository->delete($post);
        $this->entityManager->flush();
        $posts = $this->postRepository->findAll();

        static::assertCount(3, $posts);
        foreach ($posts as $postInDb) {
            if ($postInDb->getId() === $post->getId()) {
                static::fail('Oooups: it seems that we deleted the wrong post or did not deleted it at all!');
            }
        }
    }

    public function testSearch(): void
    {
        $posts = $this->postRepository->search('keyword', 10);
        static::assertCount(2, $posts);
        static::assertEquals(1, $posts[0]->id);
        static::assertEquals(3, $posts[1]->id);
    }
}
