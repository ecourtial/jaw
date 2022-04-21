<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
use App\Search\SearchResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function save(Post $post): void
    {
        $this->getEntityManager()->persist($post);
        $this->getEntityManager()->flush();
    }

    public function delete(Post $post): void
    {
        $this->getEntityManager()->remove($post);
        $this->getEntityManager()->flush();
    }

    /** @return \App\Search\SearchResult[] */
    public function search(string $keywords): array
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('m.id, m.title, m.slug, m.publishedAt, c.id as categId, c.title as categTitle, c.slug as categSlug');

        $qb->from(Post::class, 'm');
        $qb->from(Category::class, 'c');


        $qb->where('m.title' . ' LIKE :request');
        $qb->setParameter('request', "%$keywords%");
        $qb->orWhere('m.summary' . ' LIKE :request');
        $qb->setParameter('request', "%$keywords%");
        $qb->orWhere('m.content' . ' LIKE :request');
        $qb->setParameter('request', "%$keywords%");

        $qb->andWhere('m.category = c.id');

        $result = $qb->getQuery()->getResult();

        $qb->orderBy('m.publishedAt', 'DESC');
        $qb->setParameter('request', "%$keywords%");


        if (empty($result)) {
            $result = [];
        }

        $processedResult = [];

        foreach ($result as $entry) {
            $processedResult[] = new SearchResult(
                $entry['id'],
                $entry['title'],
                $entry['slug'],
                $entry['publishedAt'],
                $entry['categId'],
                $entry['categTitle'],
                $entry['categSlug'],
            );
        }

        return $processedResult;
    }
}
