<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\Subsets\Sections;

use App\Tests\Functional\Tools\UrlInterface;
use App\DataFixtures\AppFixtures;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

Trait CategoriesTrait
{
    protected function checkCategoryMenuItem(KernelBrowser $client): void
    {
        $crawler = $client->request('GET', UrlInterface::ADMIN_URL);
        $link = $crawler->selectLink('List')->link();
        $client->click($link);
        $this->assertPageTitleContains('MyBlog Admin - Categories index - JAW v1.0');
    }

    /** Check the categories listing screen */
    protected function checkCategoriesListBeforeAddingAnotherOne(KernelBrowser $client): void
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
    protected function checktDetailsOfCategories(KernelBrowser $client): void
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

        return $categories;
    }
}
