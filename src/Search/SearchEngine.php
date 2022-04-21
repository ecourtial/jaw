<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Search;

use App\Repository\PostRepository;

class SearchEngine
{
    public function __construct(private PostRepository $postRepository)
    {
    }

    /** @return \App\Entity\Post[] */
    public function search(string $keywords): array
    {
        // @TODO Add an interface over this class so we could have different implementations
        return $this->postRepository->search($keywords);
    }
}
