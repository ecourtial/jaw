<?php

namespace App\Tests\Unit\Search;

use App\Repository\PostRepository;
use App\Search\SearchEngine;
use App\Search\SearchResult;
use PHPUnit\Framework\TestCase;

class SearchEngineTest extends TestCase
{
    public function testBehavior(): void
    {
        $repository = $this->createMock(PostRepository::class);
        $searchEngine = new SearchEngine($repository);

        $query = 'some keywords';
        $result = [
            new SearchResult(1, 'd', 'd', new \DateTime(), 1, 'd', 'd'),
            new SearchResult(2, 'e', 'e', new \DateTime(), 1, 'e', 'e'),
        ];

        $repository->expects(static::once())
            ->method('search')
            ->with($query)
            ->willReturn($result);

        static::assertEquals($result, $searchEngine->search($query));
    }
}
