<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Search;

class SearchResult
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly \DateTime $publicationDate,
        public readonly int $categoryId,
        public readonly string $categoryTitle,
        public readonly string $categorySlug,
    ) {
    }
}
