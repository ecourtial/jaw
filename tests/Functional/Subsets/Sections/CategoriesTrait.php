<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\Subsets\Sections;

use App\Tests\Functional\Tools\UrlInterface;
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

    protected function checkCategoriesListBeforeAddingAnother(KernelBrowser $client): void
    {
        $crawler = $client->request('GET', UrlInterface::CATEGORIES_LIST_SCREEN_URL);
        $this->assertPageTitleContains('MyBlog Admin - Categories index - JAW v1.0');

        $result = [];
        $link = $crawler->selectLink('Details');

        $link->each(function ($node, $i) use (&$result) {
            /** @var \Symfony\Component\DomCrawler\Crawler $node */
            $entry = [
                'url' => $node->link()->getUri(),
                'lineText' => $node->ancestors()->text(),
            ];

            $result[] = $entry;

        });

        $expected = [
            [
                'url' => UrlInterface::CATEGORIES_DETAIL_SCREEN_URL_ROOT . 1,
                'lineText' => 'My first category - Details - Edit',
            ],
            [
                'url' => UrlInterface::CATEGORIES_DETAIL_SCREEN_URL_ROOT . 2,
                'lineText' => 'Another category - Details - Edit',
            ],
        ];

        static::assertEquals($expected, $result);
    }
}
