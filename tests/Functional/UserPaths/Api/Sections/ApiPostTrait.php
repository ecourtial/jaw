<?php

namespace App\Tests\Functional\UserPaths\Api\Sections;

use App\Entity\Post;
use App\Entity\User;
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

    public function checkErrorWhenTooManyFilters(KernelBrowser $client): void
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

    public function checkErrorWhenUnsupportedFilters(KernelBrowser $client): void
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

        $post = $postRepository->find(1);
        $formattedPost = $this->formatPostForExpectedApiResult($post);

        $client->request(
            'GET',
            UrlInterface::GET_POST_ENDPOINT_URL . 'id=1',
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getAdminUserToken()
            ]
        );

        static::assertEquals($formattedPost, \json_decode($client->getResponse()->getContent(), true));
    }

    private function formatPostForExpectedApiResult(Post $post): array
    {
        $result = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'summary' => $post->getSummary(),
            'publishedAt' => $post->getPublishedAt(),
            'updatedAt' => $post->getUpdatedAt(),
            'slug' => $post->getSlug(),
            'online' => $post->isOnline(),
            'topPost' => $post->isTopPost(),
            'language' => $post->getLanguage(),
            'obsolete' => $post->isObsolete(),
            'categoryId' => $post->getCategory()->getId(),
            'authorId' => $post->getAuthor()->getId(),
            'content' => $post->getContent(),
        ];

        $result['publishedAt'] = $result['publishedAt']->format(\DateTimeInterface::ATOM);
        $result['updatedAt'] = $result['updatedAt']->format(\DateTimeInterface::ATOM);

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
}
