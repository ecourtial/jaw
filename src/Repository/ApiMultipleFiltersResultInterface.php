<?php

namespace App\Repository;

interface ApiMultipleFiltersResultInterface
{
    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getByMultipleApiFilters(array $params): array;
}
