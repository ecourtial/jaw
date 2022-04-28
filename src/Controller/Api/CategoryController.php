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
class CategoryController extends AbstractSimpleFilterApiController
{
    #[Route('', methods: ['GET'], name: 'api_category_get')]
    public function getCategory(CategoryRepository $categoryRepository): JsonResponse
    {
        return $this->getResult($categoryRepository);
    }
}
