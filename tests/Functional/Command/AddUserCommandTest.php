<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Functional\Command;

use App\Command\AddUserCommand;
use App\Repository\UserRepository;

class AddUserCommandTest extends AbstractCommandTest
{
    private array $userData = [
        'username' => 'chuck_norris',
        'password' => 'foobar',
        'email' => 'chuck@norris.com',
        'full-name' => 'Chuck Norris',
    ];

    protected function setUp(): void
    {
        if ('Windows' === \PHP_OS_FAMILY) {
            $this->markTestSkipped('`stty` is required to test this command.');
        }
    }

    /**
     * @dataProvider isAdminDataProvider
     *
     * This test provides all the arguments required by the command, so the
     * command runs non-interactively and it won't ask for any argument.
     */
    public function testCreateUserNonInteractive(bool $isAdmin): void
    {
        static $entryCount = 0;

        $input = $this->userData;

        foreach ($input as &$value) {
            $value = $entryCount . $value;
        }
        unset($value);

        if ($isAdmin) {
            $input['--admin'] = 1;
        }
        $this->executeCommand($input);

        $this->assertUserCreated($isAdmin, $input);

        $entryCount++;
    }

    /**
     * This is used to execute the same test twice: first for normal users
     * (isAdmin = false) and then for admin users (isAdmin = true).
     */
    public function isAdminDataProvider(): ?\Generator
    {
        yield [false];
        yield [true];
    }

    /**
     * This helper method checks that the user was correctly created and saved
     * in the database.
     */
    private function assertUserCreated(bool $isAdmin, array $input): void
    {
        /** @var \App\Entity\User $user */
        $user = $this->getContainer()->get(UserRepository::class)->findOneByEmail($input['email']);
        $this->assertNotNull($user);

        $this->assertSame($input['full-name'], $user->getFullName());
        $this->assertSame($input['username'], $user->getUsername());
        $this->assertTrue($this->getContainer()->get('test.user_password_hasher')->isPasswordValid($user, $input['password']));
        $this->assertSame($isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER'], $user->getRoles());
    }

    protected function getCommandFqcn(): string
    {
        return AddUserCommand::class;
    }
}
