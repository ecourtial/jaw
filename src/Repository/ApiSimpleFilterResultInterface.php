<?php

namespace App\Repository;

interface ApiSimpleFilterResultInterface
{
    /**  @return array<string, mixed> */
    public function getByUniqueApiFilter(string $filter, string|int $param): array;
}
