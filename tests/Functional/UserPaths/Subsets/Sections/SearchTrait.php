<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\UserPaths\Subsets\Sections;

use App\DataFixtures\AppFixtures;
use App\Tests\Functional\TestingTools\UrlInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

Trait SearchTrait
{
    public function performSearch(KernelBrowser $client): void
    {
        $crawler = $client->request('GET', UrlInterface::ADMIN_URL);
        $form = $crawler->selectButton('searchPostsButton')->form(['query' => 'keyword']);
        $crawler = $client->submit($form);

        // Qty of results
        static::assertEquals('Search result (2)', $crawler->filter('h1')->text());
        static::assertEquals(2, count($crawler->filter("[id^=post_]")));

        // Check each entry
        $categories = AppFixtures::getFixturesCategoriesForFunctionalTesting();

        $posts = [
            $categories[0]->getPosts()[0],
            $categories[1]->getPosts()[1],
        ];

        foreach ($posts as $post) {
            $line = $crawler->filter('#post_' . $post->getId());
            $expectedText = $post->getTitle() . ' (Category: ' . $post->getCategory()->getTitle() . ') - Edit';
            static::assertEquals($expectedText, $line->text());

            $categLink = $crawler->filter('#details_categ_' . $post->getCategory()->getId());
            static::assertEquals($post->getCategory()->getTitle(), $categLink->text());
            static::assertEquals(UrlInterface::CATEGORIES_LIST_SCREEN_URL . '/' . $post->getCategory()->getId(), $categLink->link()->getUri());

            $postLink = $crawler->filter('#edit_post_' . $post->getId());
            static::assertEquals('Edit', $postLink->text());
            static::assertEquals(UrlInterface::POSTS_LIST_URL . '/' . $post->getId() . '/edit', $postLink->link()->getUri());
        }
    }
}
