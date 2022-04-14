<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\Subsets\Sections;

use App\Entity\Category;
use App\Tests\Functional\Tools\UrlInterface;
use App\DataFixtures\AppFixtures;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

Trait CategoriesTrait
{
    private ?Category $newCategory = null;

    protected function checkCategoryMenuItem(KernelBrowser $client): void
    {
        $crawler = $client->request('GET', UrlInterface::ADMIN_URL);
        $link = $crawler->selectLink('List')->link();
        $client->click($link);
        $this->assertPageTitleContains('MyBlog Admin - Categories index - JAW v1.0');
    }

    /** Check the categories listing screen */
    protected function checkCategoriesList(KernelBrowser $client): void
    {
        $crawler = $client->request('GET', UrlInterface::CATEGORIES_LIST_SCREEN_URL);
        $this->assertPageTitleContains('MyBlog Admin - Categories index - JAW v1.0');
        static::assertEquals('Categories index', $crawler->filter('h1')->text());

        foreach ($this->getFixturesCategories() as $category) {
            static::assertEquals($category->getTitle() . ' - Details - Edit', $crawler->filter('#categ_' . $category->getId())->text());

            $detailsLink = $crawler->filter('#details_categ_' . $category->getId());
            static::assertEquals('Details', $detailsLink->text());
            static::assertEquals(UrlInterface::CATEGORIES_LIST_SCREEN_URL . '/' . $category->getId(), $detailsLink->link()->getUri());

            $detailsLink = $crawler->filter('#edit_categ_' . $category->getId());
            static::assertEquals('Edit', $detailsLink->text());
            static::assertEquals(UrlInterface::CATEGORIES_LIST_SCREEN_URL . '/' . $category->getId() . '/edit', $detailsLink->link()->getUri());
        }
    }

    /** Check the category details screen */
    protected function checkDetailsOfCategories(KernelBrowser $client): void
    {
        foreach ($this->getFixturesCategories() as $category) {
            $crawler = $client->request('GET', UrlInterface::CATEGORIES_LIST_SCREEN_URL . '/' . $category->getId());

            static::assertEquals('Category: ' . $category->getTitle(), $crawler->filter('h1')->text());
            static::assertEquals('Summary: ' . $category->getSummary() , $crawler->filter('#summary')->text());
            static::assertEquals('Slug: ' . $category->getSlug() , $crawler->filter('#slug')->text());
            static::assertEquals('Id: ' . $category->getId(), $crawler->filter('#categId')->text());

            foreach ($category->getPosts() as $post) {
                static::assertEquals($post->getTitle() . ' - Edit', $crawler->filter('#post_' . $post->getId())->text());
            }
        }
    }

    /** Add a new category */
    protected function checkAddCategory(KernelBrowser $client): void
    {
        $client->followRedirects();

        // 1- Add the category
        $this->newCategory = (new Category())->setTitle('NewCateg')->setSlug('new-categ')->setSummary('A new categ');

        $crawler = $client->request('GET', UrlInterface::ADMIN_URL);
        $link = $crawler->selectLink('Add')->link();
        $crawler = $client->click($link);

        $this->assertPageTitleContains('MyBlog Admin - Create a category - JAW v1.0');
        static::assertEquals('Create a category', $crawler->filter('h1')->text());

        $form = $crawler->selectButton('saveCategorySubmitButton')->form([
            'category[title]' => $this->newCategory->getTitle(),
            'category[slug]' => $this->newCategory->getSlug(),
            'category[summary]' => $this->newCategory->getSummary(),
        ]);

        $client->submit($form);
        static::assertEquals(200, $client->getResponse()->getStatusCode());
        $crawler = $client->request('GET', UrlInterface::CATEGORIES_LIST_SCREEN_URL);
        $this->assertPageTitleContains('MyBlog Admin - Categories index - JAW v1.0');

        // 2- Get the id of the last created category (supposed to be ours), so we can do the previous checks again.
        $links = $crawler->selectLink('Details');
        $result = [];
        $links->each(function ($node, $i) use (&$result) {
            /** @var \Symfony\Component\DomCrawler\Crawler $node */
            $entry = [
                'url' => $node->link()->getUri(),
                'lineText' => $node->ancestors()->text(),
            ];

            $result[] = $entry;
        });
        $lastEntry = array_pop($result);
        $this->newCategory->setId((int)substr($lastEntry['url'], -1));

        $client->followRedirects(false);
    }

    /** Edit the newly created category */
    protected function checkEditCategory(KernelBrowser $client): void
    {
        $client->followRedirects();

        $crawler = $client->request('GET', UrlInterface::CATEGORIES_LIST_SCREEN_URL . '/' . $this->newCategory->getId() . '/edit');

        $title = 'Edit the category: ' . $this->newCategory->getTitle();
        $this->assertPageTitleContains('MyBlog Admin - ' . $title . ' - JAW v1.0');
        static::assertEquals($title, $crawler->filter('h1')->text());

        $this->newCategory->setTitle('New title');
        $this->newCategory->setSummary('New summary');
        $this->newCategory->setSummary('new-summary');

        $form = $crawler->selectButton('saveCategorySubmitButton')->form([
            'category[title]' => $this->newCategory->getTitle(),
            'category[slug]' => $this->newCategory->getSlug(),
            'category[summary]' => $this->newCategory->getSummary(),
        ]);

        $client->submit($form);
        static::assertEquals(200, $client->getResponse()->getStatusCode());
        $client->request('GET', UrlInterface::CATEGORIES_LIST_SCREEN_URL);
        $this->assertPageTitleContains('MyBlog Admin - Categories index - JAW v1.0');

        $client->followRedirects(false);
    }

    /** Delete the newly created category */
    protected function checkDeleteCategory(KernelBrowser $client): void
    {
        $client->followRedirects();

        $crawler = $client->request('GET', UrlInterface::CATEGORIES_LIST_SCREEN_URL . '/' . $this->newCategory->getId() . '/edit');

        $title = 'Edit the category: ' . $this->newCategory->getTitle();
        $this->assertPageTitleContains('MyBlog Admin - ' . $title . ' - JAW v1.0');
        static::assertEquals($title, $crawler->filter('h1')->text());

        $form = $crawler->selectButton('deleteCategorySubmitButton')->form();

        $client->submit($form);
        static::assertEquals(200, $client->getResponse()->getStatusCode());
        $client->request('GET', UrlInterface::CATEGORIES_LIST_SCREEN_URL);
        $this->assertPageTitleContains('MyBlog Admin - Categories index - JAW v1.0');

        $this->newCategory = null;

        $client->followRedirects(false);
    }

    /** Test the safety against deleting a category which has posts */
    protected function checkCannotDeleteCategoryWithPosts(KernelBrowser $client): void
    {
        $client->followRedirects();

        $crawler = $client->request('GET', UrlInterface::CATEGORIES_LIST_SCREEN_URL . '/' . 1 . '/edit');

        $title = 'Edit the category: ' . $this->getFixturesCategories()[0]->getTitle();
        $this->assertPageTitleContains('MyBlog Admin - ' . $title . ' - JAW v1.0');
        static::assertEquals($title, $crawler->filter('h1')->text());

        $form = $crawler->selectButton('deleteCategorySubmitButton')->form();

        $client->submit($form);
        static::assertEquals(200, $client->getResponse()->getStatusCode());
        $client->request('GET', UrlInterface::CATEGORIES_LIST_SCREEN_URL);
        $this->assertPageTitleContains('MyBlog Admin - Categories index - JAW v1.0');

        $client->followRedirects(false);
    }

    private function getFixturesCategories(): array
    {
        $postIndex = 1;

        $categories = AppFixtures::getFixturesCategories();
        foreach ($categories as $key => $category) {
            // We set the ids manually by guessing it (see DataFixtures structure).
            $category->setId($key + 1);

            foreach ($category->getPosts() as $post) {
                $post->setId($postIndex);
                $postIndex++;
            }
        }

        if (null !== $this->newCategory) {
            $categories[] = $this->newCategory;
        }

        return $categories;
    }
}
