<?php

namespace App\Tests\Functional\UserPaths\Api\Sections;

use App\Entity\Category;
use App\Security\ApiKeyAuthenticator;
use App\Tests\Functional\TestingTools\RequestTools;
use App\Tests\Functional\TestingTools\UrlInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait ApiCategoryTrait
{
    public function hasNoAccessToGetCategory(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_CATEGORY_ENDPOINT_URL . 'id=99',
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => 'nope'
            ]
        );

        static::assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function checkGetCategoryNotFound(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_CATEGORY_ENDPOINT_URL . 'id=99',
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

    public function checkErrorWhenTooManyFiltersForCategory(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_CATEGORY_ENDPOINT_URL . 'id=99&slug=toto',
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

    public function checkErrorWhenUnsupportedFiltersForCategory(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_CATEGORY_ENDPOINT_URL . 'foo=bar',
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

    public function checkCanAccessToGetCategory(KernelBrowser $client): void
    {
        $categories = $this->getCategories();

        // By id
        $category = $categories[0];
        $formattedCategory = $this->formatCategoryForExpectedApiResult($category);

        $client->request(
            'GET',
            UrlInterface::GET_CATEGORY_ENDPOINT_URL . 'id=' . $category->getId(),
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getAdminUserToken()
            ]
        );

        static::assertEquals($formattedCategory, \json_decode($client->getResponse()->getContent(), true));

        // By slug
        $category = $categories[1];;
        $formattedPost = $this->formatCategoryForExpectedApiResult($category);

        $client->request(
            'GET',
            UrlInterface::GET_CATEGORY_ENDPOINT_URL . 'slug=' . $category->getSlug(),
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getAdminUserToken()
            ]
        );

        static::assertEquals($formattedPost, \json_decode($client->getResponse()->getContent(), true));
    }

    private function formatCategoryForExpectedApiResult(Category $category): array
    {
        $result = [
            'id' => $category->getId(),
            'title' => $category->getTitle(),
            'summary' => $category->getSummary(),
            'createdAt' => $category->getCreatedAt(),
            'updatedAt' => $category->getUpdatedAt(),
            'slug' => $category->getSlug(),
            'postCount' => \count($category->getPosts()),
        ];

        $result['createdAt'] = $result['createdAt']->format(\DateTimeInterface::ATOM);
        $result['updatedAt'] = $result['updatedAt']->format(\DateTimeInterface::ATOM);

        return $result;
    }
}
