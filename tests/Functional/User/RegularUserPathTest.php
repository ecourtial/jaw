<?php

namespace App\Tests\Functional\User;

class RegularUserPathTest extends AbstractUserPathCase
{
    public function testRegularUserPath(): void
    {
        $client = static::createClient();

        $this->testStandardInteractions(
            $client,
            'some_username_not_admin',
            'Foofoo BARBAR',
            'foofoo@barbar.com',
            'somePassword',
            'fooRegular@stuff.com',
            'Regular Pepe the Pew',
            'someNewPassword'
        );

        // Test change blog configuration
        $this->assertCannotAccessConfigurationPanel($client);

        // Keep that in last position
        $this->logout($client);
    }
}
