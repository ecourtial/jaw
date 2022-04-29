<?php

namespace App\Repository;

interface ApiSimpleFilterResultInterface
{
    public function getByUniqueApiFilter(string $filter, string|int $param): array;
}
