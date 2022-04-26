<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeTest extends WebTestCase
{
    public function testResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $response = $client->getResponse();

        static::assertEquals(200, $response->getStatusCode());
        static::assertEquals('{"message":"Hello!"}', $response->getContent());
    }
}
