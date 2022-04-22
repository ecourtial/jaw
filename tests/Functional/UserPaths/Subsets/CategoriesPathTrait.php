<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Tests\Functional\UserPaths\Subsets;

use App\Tests\Functional\UserPaths\Subsets\Sections\CategoriesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

Trait CategoriesPathTrait
{
    use CategoriesTrait;

    public function checkCategoriesPath(KernelBrowser $client): void
    {
        $this->checkCategoryMenuItem($client);
        $this->checkCategoriesList($client);
        $this->checkDetailsOfCategories($client);

        $this->checkAddCategory($client);
        $this->checkCategoriesList($client);
        $this->checkDetailsOfCategories($client);

        $this->checkEditCategory($client);
        $this->checkCategoriesList($client);
        $this->checkDetailsOfCategories($client);

        $this->checkDeleteCategory($client);
        $this->checkCategoriesList($client);
        $this->checkDetailsOfCategories($client);

        $this->checkCannotDeleteCategoryWithPosts($client);
        $this->checkCategoriesList($client);
        $this->checkDetailsOfCategories($client);
    }
}