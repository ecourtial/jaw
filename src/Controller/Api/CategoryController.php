<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/category')]
class CategoryController extends AbstractFilteredResultApiController
{
    #[Route('', methods: ['GET'], name: 'api_category_get')]
    public function getCategory(CategoryRepository $categoryRepository): JsonResponse
    {
        return $this->getResultForUniqueFilter($categoryRepository);
    }

    #[Route('/search', methods: ['GET'], name: 'api_category_search')]
    public function search(CategoryRepository $categoryRepository): JsonResponse
    {
        return $this->getResultForMultipleFilters($categoryRepository);
    }
}
