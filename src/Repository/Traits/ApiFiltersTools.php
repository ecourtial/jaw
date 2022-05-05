<?php

namespace App\Repository\Traits;

use Doctrine\ORM\NoResultException;

trait ApiFiltersTools
{
    /**
     * Filter on fields that are UNIQUE in the entity
     *
     * @return string[]
     */
    public function getByUniqueApiFilter(string $filter, string|int $param): array
    {
        if ($filter === 'slug' || $filter === 'id') {
            $result = $this->getByMultipleApiFilters([$filter => $param]);
            if (0 === $result['resultCount']) {
                throw new NoResultException();
            }

            return $result['results'][0];
        } else {
            throw new \LogicException('Unsupported filter: ' . $filter);
        }
    }

    /**
     * Creates the middle part of the query to perform a multi-filters search.
     *
     * @param array<string, mixed> $params
     *
     * @return array<int, string|mixed>
     */
    private function initQueryWithMultipleFilters(array $params): array
    {
        $queryParams = [];
        // Creation of the second part of the query (the conditions)
        $query = '';

        // Basic filters on value
        foreach ($params as $filter => $value) {
            if (true === \array_key_exists($filter, self::AVAILABLE_COLUMN_FILTERS)) {
                if (self::AVAILABLE_COLUMN_FILTERS[$filter]['source'] === 'column'
                    || self::AVAILABLE_COLUMN_FILTERS[$filter]['source'] === 'computed'
                ) {
                    $query .= "AND $filter = :$filter ";
                } elseif (self::AVAILABLE_COLUMN_FILTERS[$filter]['source'] === 'alias') {
                    $query .= 'AND ' . self::AVAILABLE_COLUMN_FILTERS[$filter]['columnName'] . " = :$filter ";
                } else {
                    continue;
                }

                $queryParams[$filter] = $value;
            }
        }

        return [$query, $queryParams];
    }

    /**
     * Processes the query to perform a multi-filters search. It uses $query, the part created in
     * initQueryWithMultipleFilters(), adds ordering and also manage the pagination.
     *
     * @param array<string, mixed> $params
     * @param array<string, mixed> $queryParams
     *
     * @return array<string, mixed>
     */
    private function executeQueryWithMultipleFilters(string $tableName, array $params, string $query, array $queryParams): array
    {
        // Counting result
        $countQuery = 'SELECT COUNT(*) as count FROM ' . $tableName . ' WHERE id IS NOT NULL ' . $query;

        $totalResultCount = $this
            ->getEntityManager()
            ->getConnection()
            ->executeQuery($countQuery, $queryParams)
            ->fetchAssociative()['count'];

        // Now: ordering
        $this->addOrderForFiltering($query, $params);

        // Pagination\
        $page = 1;
        $limit = 10;

        if (\array_key_exists('limit', $params)) {
            $limit = (int)$params['limit'];
        }

        $totalPageCount = (int)ceil($totalResultCount/$limit);
        $query .= "LIMIT $limit ";

        if (\array_key_exists('page', $params)) {
            $page = (int)$params['page'];
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page * $limit) - $limit;
            $query .= "OFFSET $offset ";
        }

        $totalPageCount = ($totalResultCount === 0) ? 1 : $totalPageCount;

        // Result
        $result = $this
            ->getEntityManager()
            ->getConnection()
            ->executeQuery($this->getMainSelectQuery($params) . $query, $queryParams);

        $entries = [];

        while ($entry = $result->fetchAssociative()) {
            $entries[] = $this->formatForApiResponse($entry);
        }

        return [
            'resultCount' => \count($entries),
            'totalResultCount' => $totalResultCount,
            'page' => $page,
            'totalPageCount' => $totalPageCount,
            'results' => $entries,
        ];
    }

    /** @param array<string, mixed> $params */
    private function addOrderForFiltering(string &$query, array $params): void
    {
        if (true === \array_key_exists('orderByField', $params)
            && true === \is_array($params['orderByField'])
        ) {
            foreach ($params['orderByField'] as $column => $order) {
                $order = strtoupper($order);

                if (true === \array_key_exists($column, self::AVAILABLE_COLUMN_FILTERS)
                    && true === \in_array($order, ['ASC', 'DESC'])
                ) {
                    if (self::AVAILABLE_COLUMN_FILTERS[$column]['source'] === 'alias') {
                        $column = self::AVAILABLE_COLUMN_FILTERS[$column]['columnName'];
                    }

                    $query .= "ORDER BY $column $order ";
                }
            }
        }
    }
}
