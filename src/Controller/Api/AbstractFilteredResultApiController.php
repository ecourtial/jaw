<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractJawController;
use App\Repository\ApiMultipleFiltersResultInterface;
use App\Repository\ApiSimpleFilterResultInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\JsonResponse;

class AbstractFilteredResultApiController extends AbstractJawController
{
    public function getResultForUniqueFilter(ApiSimpleFilterResultInterface $repository): JsonResponse
    {
        $queryParamsCount = count($this->request->query);
        if ($queryParamsCount > 1) {
            return new JsonResponse(['message' => "Only one filter can be accepted, $queryParamsCount given."], 400);
        }

        $id = (int)$this->request->get('id');
        $slug = $this->request->get('slug', '');

        $filter = '';
        $param = '';

        if ($id !== 0) {
            $filter = 'id';
            $param = $id;
        }

        if ($slug !== '') {
            $filter = 'slug';
            $param = $slug;
        }

        try {
            $response = new JsonResponse($repository->getByUniqueApiFilter($filter, $param));
        } catch (NoResultException $exception) {
            $response = new JsonResponse(['message' => 'No result found.'], 404);
        } catch (\LogicException $exception) {
            $response = new JsonResponse(['message' => 'No supported filter was given. Available filters are: id, slug.'], 400);
        }

        return $response;
    }

    public function getResultForMultipleFilters(ApiMultipleFiltersResultInterface $repository): JsonResponse
    {
        return new JsonResponse($repository->getByMultipleApiFilters($this->request->query->all()));
    }
}
