<?php

namespace App\Tests\Functional\TestingTools;

class HeaderTools
{
    public static function formatCustomHeaderName(string $headerName): string
    {
        return str_replace('-', '_', 'HTTP_' . $headerName);
    }
}
