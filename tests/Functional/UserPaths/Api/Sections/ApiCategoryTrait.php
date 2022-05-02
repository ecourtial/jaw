<?php

namespace App\Tests\Functional\UserPaths\Api\Sections;

use App\Entity\Category;
use App\Repository\CategoryRepository;
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

    public function checkCantSearchCategoriesIfNotAuthenticated(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_SEARCH_CATEGORY_ENDPOINT_URL . 'id=99',
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => 'nope'
            ]
        );

        static::assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function checkSearchCategoriesWithNoResult(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_SEARCH_CATEGORY_ENDPOINT_URL . 'id=99',
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
            "categories" => []
        ];
        static::assertEquals($expectedResult, $result);
    }

    public function checkSearchCategoriesWithResult(KernelBrowser $client): void
    {
        $client->request(
            'GET',
            UrlInterface::GET_SEARCH_CATEGORY_ENDPOINT_URL . 'id=2',
            [],
            [],
            [
                RequestTools::formatCustomHeaderName(ApiKeyAuthenticator::API_TOKEN_HEADER_NAME) => $this->getAdminUserToken()
            ]
        );

        $result = \json_decode($client->getResponse()->getContent(), true);
        $expectedResult = [
            "resultCount" => 1,
            "totalResultCount" => 1,
            "page"=> 1,
            "totalPageCount" => 1,
            "categories" => [
                $this->formatCategoryForExpectedApiResult($this->getCategoryRepository()->find(2)),
            ]
        ];
        static::assertEquals($expectedResult, $result);
    }

    private function formatCategoryForExpectedApiResult(Category $category): array
    {
        $result = [
            'id' => $category->getId(),
            'title' => $category->getTitle(),
            'summary' => $category->getSummary(),
            'slug' => $category->getSlug(),
            'postCount' => \count($category->getPosts()),
            'createdAt' => $category->getCreatedAt(),
            'updatedAt' => $category->getUpdatedAt(),
        ];

        $result['createdAt'] = $result['createdAt']->format(\DateTimeInterface::ATOM);
        $result['updatedAt'] = $result['updatedAt']->format(\DateTimeInterface::ATOM);

        return $result;
    }

    private function getCategoryRepository(): CategoryRepository
    {
        return static::getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Category::class);
    }
}
