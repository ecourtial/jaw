<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Category;
use App\Entity\Post;
use App\Exception\Category\CategoryNotEmptyException;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryRepositoryTest extends KernelTestCase
{
    private CategoryRepository $categoryRepository;

    public function setup(): void
    {
        $kernel = self::bootKernel();

        $this->categoryRepository = $kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Category::class);
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

        // Listing

        $categories = $this->categoryRepository->listAll();

        static::assertCount(3, $categories);
        static::assertEquals($category, $categories[2]);

        // Delete

        $this->categoryRepository->delete($category);

        $categories = $this->categoryRepository->listAll();

        static::assertCount(2, $categories);
        foreach ($categories as $categoryInDb) {
            if ($categoryInDb->getId() === $category->getId()) {
                static::fail('Oooups: it seems that we deleted the wrong category or did not deleted it at all!');
            }
        }

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
}
