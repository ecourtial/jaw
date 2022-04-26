<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\UserPaths\Admin;

use App\Tests\Functional\UserPaths\Admin\Sections\SearchTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

Trait SearchPathTrait
{
    use SearchTrait;

    public function searchPosts(KernelBrowser $client): void
    {
        $this->performSearch($client);
    }
}
