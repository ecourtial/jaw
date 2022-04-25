<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Webhook;
use App\Event\ResourceEvent;
use App\Search\SearchResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($registry, Post::class);
    }

    public function save(Post $post): void
    {
        $actionType = ($post->getId() === null) ? Webhook::RESOURCE_ACTION_CREATION : Webhook::RESOURCE_ACTION_EDITION;
        $this->getEntityManager()->persist($post);
        $this->getEntityManager()->flush();
        $this->eventDispatcher->dispatch(new ResourceEvent($post, $actionType), ResourceEvent::NAME);
    }

    public function delete(Post $post): void
    {
        $this->eventDispatcher->dispatch(new ResourceEvent($post, Webhook::RESOURCE_ACTION_DELETION), ResourceEvent::NAME);
        $this->getEntityManager()->remove($post);
        $this->getEntityManager()->flush();
    }

    /** @return \App\Search\SearchResult[] */
    public function search(string $keywords, int $limit): array
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('m.id, m.title, m.slug, m.publishedAt, c.id as categId, c.title as categTitle, c.slug as categSlug');

        $qb->from(Post::class, 'm');
        $qb->from(Category::class, 'c');

        $qb->where('m.title' . ' LIKE :request');
        $qb->orWhere('m.summary' . ' LIKE :request');
        $qb->orWhere('m.content' . ' LIKE :request');
        $qb->andWhere('m.category = c.id');
        $qb->orderBy('m.publishedAt', 'DESC');
        $qb->setMaxResults($limit);
        $qb->setParameter('request', "%$keywords%");

        $result = $qb->getQuery()->getResult();

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
