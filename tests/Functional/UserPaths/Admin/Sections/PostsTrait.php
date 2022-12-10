<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\UserPaths\Admin\Sections;

use App\Entity\Category;
use App\Entity\Post;
use App\Tests\Functional\TestingTools\UrlInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

trait PostsTrait
{
    private ?Post $newPost;

    public function createPost(KernelBrowser $client): void
    {
        $client->followRedirects();

        // 1- Add the post
        $this->newPost = (new Post())
            ->setOnline(true)
            ->setTopPost(true)
            ->setObsolete(false)
            ->setCategory((new Category())->setId(1))
            ->setContent('The awesome content of the post')
            ->setTitle('The title of the post')
            ->setSummary('The summary of the post')
            ->setSlug('the-slug-of-the-post')
            ->setLanguage('en');


        $crawler = $client->request('GET', UrlInterface::ADMIN_URL);
        $link = $crawler->selectLink('Create post')->link();
        $crawler = $client->click($link);

        $this->assertPageTitleContains('MyBlog Admin - Create a post - JAW v1.1.0');
        static::assertEquals('Create a post', $crawler->filter('h1')->text());

        $form = $crawler->selectButton('savePostSubmitButton')->form([
            'post[title]' => $this->newPost->getTitle(),
            'post[category]' => $this->newPost->getCategory()->getId(),
            'post[online]' => (int)$this->newPost->isOnline(),
            'post[obsolete]' => (int)$this->newPost->isObsolete(),
            'post[toppost]' => (int)$this->newPost->isTopPost(),
            'post[language]' => $this->newPost->getLanguage(),
            'post[summary]' => $this->newPost->getSummary(),
            'post[content]' => $this->newPost->getContent(),
        ]);

        $crawler = $client->submit($form);

        static::assertEquals(200, $client->getResponse()->getStatusCode());

        // Note that if we go back to the create form it MAY mean that the form is not valid.
        $this->assertPageTitleContains('MyBlog Admin - Edit the post: ' . $this->newPost->getTitle() . ' - JAW v1.1.0');
        static::assertEquals('Edit the post: ' . $this->newPost->getTitle(), $crawler->filter('h1')->text());

        // Load the post to get the fully hydrated object
        $this->newPost = self::getContainer()->get('App\Repository\PostRepository')->find($this->getIdFromEditPage($crawler));

        $client->followRedirects(false);
    }

    public function editPost(KernelBrowser $client, int $categId): void
    {
        $client->followRedirects();

        // The category id is hardcoded on purpose to check that the edition is done with success.
        $crawler = $client->request('GET', UrlInterface::CATEGORIES_LIST_SCREEN_URL . '/' . $categId);
        $link = $crawler->filter('#edit_post_' . $this->newPost->getId())->link();
        $crawler = $client->click($link);

        $this->assertPageTitleContains('MyBlog Admin - Edit the post: ' . $this->newPost->getTitle() . ' - JAW v1.1.0');
        static::assertEquals('Edit the post: ' . $this->newPost->getTitle(), $crawler->filter('h1')->text());
        static::assertEquals($this->newPost->getId(), $this->getIdFromEditPage($crawler));
        static::assertEquals('Creation date: ' . $this->newPost->getCreatedAt()->format('Y-m-d H:i:s'), $crawler->filter('#creationDate')->text());
        static::assertEquals('Last modification: ' . $this->newPost->getUpdatedAt()->format('Y-m-d H:i:s'), $crawler->filter('#updateDate')->text());

        // Check the form content
        $formValues = $crawler->selectButton('savePostSubmitButton')->form()->getPhpValues();

        static::assertEquals($this->newPost->getTitle(), $formValues['post']['title']);
        static::assertEquals($this->newPost->getCategory()->getId(), $formValues['post']['category']);
        static::assertEquals((int)$this->newPost->isOnline(), $formValues['post']['online']);
        static::assertEquals((int)$this->newPost->isTopPost(), $formValues['post']['toppost']);
        static::assertEquals($this->newPost->getLanguage(), $formValues['post']['language']);
        static::assertEquals($this->newPost->getSummary(), $formValues['post']['summary']);
        static::assertEquals($this->newPost->getContent(), $formValues['post']['content']);

        // Edit the post
        $this->newPost
            ->setOnline(false)
            ->setTopPost(false)
            ->setObsolete(true)
            ->setCategory((new Category())->setId(2))
            ->setContent('The new awesome content of the post')
            ->setTitle('The new title of the post')
            ->setSummary('The new summary of the post')
            ->setSlug('the-new-slug-of-the-post')
            ->setLanguage('fr');

        $form = $crawler->selectButton('savePostSubmitButton')->form([
            'post[title]' => $this->newPost->getTitle(),
            'post[category]' => $this->newPost->getCategory()->getId(),
            'post[online]' => (int)$this->newPost->isOnline(),
            'post[obsolete]' => (int)$this->newPost->isObsolete(),
            'post[toppost]' => (int)$this->newPost->isTopPost(),
            'post[language]' => $this->newPost->getLanguage(),
            'post[summary]' => $this->newPost->getSummary(),
            'post[content]' => $this->newPost->getContent(),
        ]);

        $client->submit($form);

        static::assertEquals(200, $client->getResponse()->getStatusCode());

        // Reload the post
        $this->newPost = self::getContainer()->get('App\Repository\PostRepository')->find($this->getIdFromEditPage($crawler));

        $client->followRedirects(false);
    }

    public function deletePost(KernelBrowser $client): void
    {
        $client->followRedirects();

        $crawler = $client->request('GET', UrlInterface::CATEGORIES_LIST_SCREEN_URL . '/' . $this->newPost->getCategory()->getId());
        $link = $crawler->filter('#edit_post_' . $this->newPost->getId())->link();
        $crawler = $client->click($link);

        $form = $crawler->selectButton('deletePostSubmitButton')->form();
        $crawler = $client->submit($form);

        static::assertEquals(UrlInterface::CATEGORIES_LIST_SCREEN_URL, $crawler->getUri());

        $client->request('GET', UrlInterface::POSTS_LIST_URL . '/' . $this->newPost->getId() . '/edit');
        static::assertEquals(404, $client->getResponse()->getStatusCode());

        $client->followRedirects(false);
    }

    private function getIdFromEditPage(Crawler $crawler): int
    {
        $idText = $crawler->filter('#postId')->text();
        $array = explode(' ', $idText);

        return (int)$array[1];
    }
}
