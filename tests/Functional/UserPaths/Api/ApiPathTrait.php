<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\UserPaths\Api;

use App\Tests\Functional\UserPaths\Api\Sections\ApiConfigurationTrait;
use App\Tests\Functional\UserPaths\Api\Sections\ApiPostTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

Trait ApiPathTrait
{
    use ApiConfigurationTrait;
    use ApiPostTrait;

    public function checkApiRoutes(KernelBrowser $client): void
    {
        // Configuration
        $this->hasNoAccessToConfigurationEndpoint($client);
        $this->hasAccessToConfigurationEndpoint($client);

        // Posts
        $this->hasNoAccessToGetPost($client);
        $this->checkGetPostNotFound($client);
        $this->checkCanAccessToGetPost($client);
    }
}
