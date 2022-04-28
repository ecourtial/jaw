<?php

namespace App\Repository;

interface ApiFilterableResultInterface
{
    public function getByApiFilter(string $filter, string|int $param): array;
}
