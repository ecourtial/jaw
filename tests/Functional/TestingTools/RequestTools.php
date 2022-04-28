<?php

namespace App\Tests\Functional\TestingTools;

class RequestTools
{
    public static function formatCustomHeaderName(string $headerName): string
    {
        return str_replace('-', '_', 'HTTP_' . $headerName);
    }

    public static function formatEndpointUrl(string $url, array $parameters): string
    {
        foreach ($parameters as $varName => $varValue) {
            $url = str_replace($varName, $varValue, 'HTTP_' . $url);
        }

        return $url;
    }
}
