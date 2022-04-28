<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\UserPaths\Api;

use App\Tests\Functional\UserPaths\Api\Sections\ApiCategoryTrait;
use App\Tests\Functional\UserPaths\Api\Sections\ApiConfigurationTrait;
use App\Tests\Functional\UserPaths\Api\Sections\ApiPostTrait;
use App\Tests\Functional\UserPaths\Api\Sections\ApiUserPathTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait ApiPathTrait
{
    use ApiConfigurationTrait;
    use ApiPostTrait;
    use ApiUserPathTrait;
    use ApiCategoryTrait;

    public function checkApiRoutes(KernelBrowser $client): void
    {
        // Configuration
        $this->hasNoAccessToConfigurationEndpoint($client);
        $this->hasAccessToConfigurationEndpoint($client);

        // Posts
        $this->hasNoAccessToGetPost($client);
        $this->checkGetPostNotFound($client);
        $this->checkErrorWhenTooManyFiltersForPost($client);
        $this->checkErrorWhenUnsupportedFiltersForPost($client);
        $this->checkCanAccessToGetPost($client);

        // Users
        $this->checkCannotAccessUser($client);
        $this->checkCannotAccessUserBecauseConnectedUserIsNotAdmin($client);
        $this->checkUserNotFound($client);
        $this->checkUserFoundWithAdminUserConnected($client);

        // Categories
        $this->hasNoAccessToGetCategory($client);
        $this->checkGetCategoryNotFound($client);
        $this->checkErrorWhenTooManyFiltersForCategory($client);
        $this->checkErrorWhenUnsupportedFiltersForCategory($client);
        $this->checkCanAccessToGetCategory($client);
    }
}
