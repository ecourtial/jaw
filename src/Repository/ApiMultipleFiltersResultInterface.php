<?php

namespace App\Repository;

interface ApiMultipleFiltersResultInterface
{
    public function getByMultipleApiFilters(array $params): array;
}
