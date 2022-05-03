<?php

namespace App\Tests\Functional\UserPaths\Api\Sections;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Security\ApiKeyAuthenticator;
use App\Tests\Functional\TestingTools\RequestTools;
use App\Tests\Functional\TestingTools\UrlInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait ApiPostTrait
{
    public function hasNoAccessToGetPost(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_POST_ENDPOINT_URL . 'id=99',
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => 'nope'
            ]
        );

        static::assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function checkGetPostNotFound(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_POST_ENDPOINT_URL . 'id=99',
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getAdminUserToken()
            ]
        );

        static::assertEquals(404, $client->getResponse()->getStatusCode());
        $expected = ['message' => 'No result found.'];
        static::assertEquals($expected, \json_decode($client->getResponse()->getContent(), true));
    }

    public function checkErrorWhenTooManyFiltersForPost(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_POST_ENDPOINT_URL . 'id=99&slug=toto',
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getAdminUserToken()
            ]
        );

        static::assertEquals(400, $client->getResponse()->getStatusCode());
        $expected = ['message' => 'Only one filter can be accepted, 2 given.'];
        static::assertEquals($expected, \json_decode($client->getResponse()->getContent(), true));
    }

    public function checkErrorWhenUnsupportedFiltersForPost(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_POST_ENDPOINT_URL . 'foo=bar',
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getAdminUserToken()
            ]
        );

        static::assertEquals(400, $client->getResponse()->getStatusCode());
        $expected = ['message' => 'No supported filter was given. Available filters are: id, slug.'];
        static::assertEquals($expected, \json_decode($client->getResponse()->getContent(), true));
    }

    public function checkCanAccessToGetPost(KernelBrowser $client): void
    {
        // By id
        $postRepository = static::getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Post::class);

        $post = $postRepository->find(1); // Published
        $formattedPost = $this->formatPostForExpectedApiResult($post);

        $client->request(
            'GET',
            UrlInterface::GET_POST_ENDPOINT_URL . 'id=' . $post->getId(),
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getAdminUserToken()
            ]
        );

        static::assertEquals($formattedPost, \json_decode($client->getResponse()->getContent(), true));

        // By slug
        $post = $postRepository->find(3); // Not published yet
        $formattedPost = $this->formatPostForExpectedApiResult($post);

        $client->request(
            'GET',
            UrlInterface::GET_POST_ENDPOINT_URL . 'slug=' . $post->getSlug(),
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getAdminUserToken()
            ]
        );

        static::assertEquals($formattedPost, \json_decode($client->getResponse()->getContent(), true));
    }

    public function checkCantSearchPostsIfNotAuthenticated(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_SEARCH_POST_ENDPOINT_URL . 'id=99',
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => 'nope'
            ]
        );

        static::assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function checkSearchPostsWithNoResult(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_SEARCH_POST_ENDPOINT_URL . 'id=99',
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getAdminUserToken()
            ]
        );

        $result = \json_decode($client->getResponse()->getContent(), true);
        $expectedResult = [
            "resultCount" => 0,
            "totalResultCount" => 0,
            "page"=> 1,
            "totalPageCount" => 1,
            "results" => []
        ];
        static::assertEquals($expectedResult, $result);
    }

    public function checkSearchPostsWithResult(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_SEARCH_POST_ENDPOINT_URL . 'category=2',
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getAdminUserToken()
            ]
        );

        $result = \json_decode($client->getResponse()->getContent(), true);
        $expectedResult = [
            "resultCount" => 2,
            "totalResultCount" => 2,
            "page"=> 1,
            "totalPageCount" => 1,
            "results" => [
                $this->formatPostForExpectedApiResult($this->getPostRepository()->find(2)),
                $this->formatPostForExpectedApiResult($this->getPostRepository()->find(3)),
            ]
        ];
        static::assertEquals($expectedResult, $result);
    }

    private function formatPostForExpectedApiResult(Post $post): array
    {
        $result = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'summary' => $post->getSummary(),
            'slug' => $post->getSlug(),
            'online' => $post->isOnline(),
            'language' => $post->getLanguage(),
            'obsolete' => $post->isObsolete(),
            'content' => $post->getContent(),
            'createdAt' => $post->getCreatedAt(),
            'updatedAt' => $post->getUpdatedAt(),
            'publishedAt' => $post->getPublishedAt(),
            'topPost' => $post->isTopPost(),
            'categoryId' => $post->getCategory()->getId(),
            'authorId' => $post->getAuthor()->getId(),
        ];

        $result['createdAt'] = $result['createdAt']->format(\DateTimeInterface::ATOM);
        $result['updatedAt'] = $result['updatedAt']->format(\DateTimeInterface::ATOM);
        $result['publishedAt'] =  $result['publishedAt'] === null ? null:$result['publishedAt']->format(\DateTimeInterface::ATOM);

        return $result;
    }

    private function getAdminUserToken(): string
    {
        $userRepository = static::getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(User::class);

        return $userRepository->find(1)->getToken();
    }

    private function getRegularUserToken(): string
    {
        $userRepository = static::getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(User::class);

        return $userRepository->find(2)->getToken();
    }

    private function getUser(int $userId): User
    {
        $userRepository = static::getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(User::class);

        return $userRepository->find($userId);
    }

    private function getPostRepository(): PostRepository
    {
        return static::getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Post::class);
    }
}
