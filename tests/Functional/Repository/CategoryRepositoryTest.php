<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Webhook;
use App\Exception\Category\CategoryNotEmptyException;
use App\Repository\CategoryRepository;
use App\Repository\WebhookRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryRepositoryTest extends KernelTestCase
{
    private CategoryRepository $categoryRepository;
    private WebhookRepository $webhookRepository;

    public function setup(): void
    {
        $kernel = self::bootKernel();

        $this->categoryRepository = $kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Category::class);

        $this->webhookRepository = $kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Webhook::class);
    }


    public function testBehavior(): void
    {
        // Creation

        $category = (new Category())
            ->setTitle('The Title')
            ->setSummary('The Summary')
            ->setSlug('the-slug');

        $this->categoryRepository->save($category);

        static::assertTrue(is_int($category->getId()));
        static::assertCount(1, $this->webhookRepository->findBy([
            'resourceId' => $category->getId(),
            'action' => Webhook::RESOURCE_ACTION_CREATION,
            'resourceType' => $category->getResourceType()
        ]));

        // Listing

        $categories = $this->categoryRepository->listAll();

        static::assertCount(3, $categories);
        static::assertEquals($category, $categories[2]);

        // Delete

        $categId = $category->getId();
        $this->categoryRepository->delete($category);

        $categories = $this->categoryRepository->listAll();

        static::assertCount(2, $categories);
        foreach ($categories as $categoryInDb) {
            if ($categoryInDb->getId() === $category->getId()) {
                static::fail('Oooups: it seems that we deleted the wrong category or did not deleted it at all!');
            }
        }

        static::assertCount(1, $this->webhookRepository->findBy(['resourceId' => $categId, 'action' => Webhook::RESOURCE_ACTION_DELETION]));

        // Delete is not possible

        $category = (new Category())
            ->setTitle('The Titleeeeee')
            ->setSummary('The Summaryyyyyy')
            ->setSlug('the-sluggggg');

        $dummyPost = $this->createMock(Post::class);

        $category->addPost($dummyPost);
        $category->setTitle('Hum');

        static::expectException(CategoryNotEmptyException::class);

        $this->categoryRepository->delete($category);
    }

    public function testGetByUniqueApiFilter(): void
    {
        $post = $this->categoryRepository->getByUniqueApiFilter('slug', 'another-category');
        static::assertEquals('another-category', $post['slug']);

        $post = $this->categoryRepository->getByUniqueApiFilter('id', 1);
        static::assertEquals('my-first-category', $post['slug']);

        static::expectExceptionMessage('Unsupported filter: foo');
        $this->categoryRepository->getByUniqueApiFilter('foo', 1);
    }

    public function testGetByMultipleApiFilters(): void
    {
        $params = [
            'content' => 1, // Must be ignored
            'page' => 2,
            'limit' => 1,
            'orderByField' => [
                'title' => 'DESC'
            ]
        ];

        $categories = $this->categoryRepository->getByMultipleApiFilters($params);

        static::assertEquals(1, $categories['resultCount']);
        static::assertEquals(2, $categories['totalResultCount']);
        static::assertEquals(2, $categories['page']);
        static::assertEquals(2, $categories['totalPageCount']);
        static::assertCount(1, $categories['results']);
        static::assertEquals(2, $categories['results'][0]['id']);

        $params = [
            'content' => 1, // Must be ignored
            'orderByField' => [
                'title' => 'DESC'
            ]
        ];

        $categories = $this->categoryRepository->getByMultipleApiFilters($params);

        static::assertEquals(2, $categories['resultCount']);
        static::assertEquals(2, $categories['totalResultCount']);
        static::assertEquals(1, $categories['page']);
        static::assertEquals(1, $categories['totalPageCount']);
        static::assertCount(2, $categories['results']);
        static::assertEquals(1, $categories['results'][0]['id']);
        static::assertEquals(2, $categories['results'][1]['id']);

        $params = [
            'orderByField' => [
                'postCount' => 'DESC'
            ]
        ];

        $categories = $this->categoryRepository->getByMultipleApiFilters($params);

        static::assertEquals(2, $categories['resultCount']);
        static::assertEquals(2, $categories['totalResultCount']);
        static::assertEquals(1, $categories['page']);
        static::assertEquals(1, $categories['totalPageCount']);
        static::assertCount(2, $categories['results']);
        static::assertEquals(2, $categories['results'][0]['id']);
        static::assertEquals(1, $categories['results'][1]['id']);

        $params = [
            'slug' => 'foo'
        ];

        $categories = $this->categoryRepository->getByMultipleApiFilters($params);

        static::assertEquals(0, $categories['resultCount']);
        static::assertEquals(0, $categories['totalResultCount']);
        static::assertEquals(1, $categories['page']);
        static::assertEquals(1, $categories['totalPageCount']);
        static::assertCount(0, $categories['results']);
    }
}
