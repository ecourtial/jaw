<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Webhook;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\WebhookRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PostRepositoryTest extends KernelTestCase
{
    private CategoryRepository $categoryRepository;
    private PostRepository $postRepository;
    private UserRepository $userRepository;
    private WebhookRepository $webhookRepository;

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

        $this->webhookRepository = $kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Webhook::class);
    }


    public function testCreateAndDelete(): void
    {
        // Creation

        $post = (new Post())
            ->setTitle('The Title')
            ->setSummary('The Summary')
            ->setSlug('the-slug')
            ->setObsolete(false)
            ->setOnline(true)
            ->setTopPost(true)
            ->setContent('The content')
            ->setLanguage('fr')
            ->setAuthor($this->userRepository->find(1))
            ->setCategory($this->categoryRepository->find(1));

        $this->postRepository->save($post);

        static::assertTrue(is_int($post->getId()));
        static::assertNotNull(is_int($post->getPublishedAt()));
        static::assertCount(1, $this->webhookRepository->findBy([
            'resourceId' => $post->getId(),
            'action' => Webhook::RESOURCE_ACTION_CREATION,
            'resourceType' => $post->getResourceType()
        ]));

        // Delete

        $postId = $post->getId();
        $this->postRepository->delete($post);
        $posts = $this->postRepository->findAll();

        static::assertCount(3, $posts);
        foreach ($posts as $postInDb) {
            if ($postInDb->getId() === $post->getId()) {
                static::fail('Oooups: it seems that we deleted the wrong post or did not deleted it at all!');
            }
        }

        static::assertCount(1, $this->webhookRepository->findBy(['resourceId' => $postId, 'action' => Webhook::RESOURCE_ACTION_DELETION]));
    }

    public function testSearch(): void
    {
        $posts = $this->postRepository->search('keyword', 10);
        static::assertCount(2, $posts);
        static::assertEquals(1, $posts[0]->id);
        static::assertEquals(3, $posts[1]->id);
    }

    public function testGetByApiFilter(): void
    {
        $post = $this->postRepository->getByUniqueApiFilter('slug', 'my_second_post');
        static::assertEquals('my_second_post', $post['slug']);

        $post = $this->postRepository->getByUniqueApiFilter('id', 1);
        static::assertEquals('my_first_post', $post['slug']);

        static::expectExceptionMessage('Unsupported filter: foo');
        $this->postRepository->getByUniqueApiFilter('foo', 1);
    }
}
