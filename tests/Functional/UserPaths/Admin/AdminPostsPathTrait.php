<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\UserPaths\Admin;

use App\Tests\Functional\UserPaths\Admin\Sections\PostsTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AdminPostsPathTrait
{
    use PostsTrait;

    public function checkPostsPath(KernelBrowser $client): void
    {
        $this->createPost($client);
        $this->editPost($client, 1);
        $this->editPost($client, 2);
        $this->deletePost($client);
    }
}
