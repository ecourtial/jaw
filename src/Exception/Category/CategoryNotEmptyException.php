<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Exception\Category;

class CategoryNotEmptyException extends \Exception
{
    public function __construct()
    {
        parent::__construct('The category cannot be deleted because it still contains some posts');
    }
}
