<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\Subsets;

use App\Tests\Functional\Subsets\Sections\PostsTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

Trait PostsPathTrait
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
