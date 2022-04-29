<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Webhook;
use App\Event\ResourceEvent;
use App\Search\SearchResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostRepository extends ServiceEntityRepository implements ApiSimpleFilterResultInterface, ApiMultipleFiltersResultInterface
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
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('p.id, p.title, p.slug, p.createdAt, p.updatedAt, p.publishedAt, c.id as categId, c.title as categTitle, c.slug as categSlug');

        $qb->from(Post::class, 'p');
        $qb->from(Category::class, 'c');

        $qb->where('p.title' . ' LIKE :request');
        $qb->orWhere('p.summary' . ' LIKE :request');
        $qb->orWhere('p.content' . ' LIKE :request');
        $qb->andWhere('p.category = c.id');
        $qb->orderBy('p.createdAt', 'DESC');
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
                $entry['createdAt'],
                $entry['updatedAt'],
                $entry['publishedAt'],
                $entry['categId'],
                $entry['categTitle'],
                $entry['categSlug'],
            );
        }

        return $processedResult;
    }

    public function getByUniqueApiFilter(string $filter, string|int $param): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select(
            'p.id, p.title, p.summary, p.createdAt, p.updatedAt, p.publishedAt, p.slug, p.online, p.topPost, '
            . 'p.language, p.obsolete, IDENTITY(p.category) as categoryId, IDENTITY(p.author) as authorId, p.content'
        );
        $qb->from(Post::class, 'p');

        if ($filter === 'slug') {
            $qb->where('p.slug' . ' LIKE :param');
            $qb->setParameter('param', "%$param%");
        } elseif ($filter === 'id') {
            $qb->where('p.id = :param');
            $qb->setParameter('param', $param);
        } else {
            throw new \LogicException('Unsupported filter: ' . $filter);
        }

        $result = $qb->getQuery()->getSingleResult();

        $result['createdAt'] = $result['createdAt']->format(\DateTimeInterface::ATOM);
        $result['updatedAt'] = $result['updatedAt']->format(\DateTimeInterface::ATOM);
        $result['publishedAt'] =  $result['publishedAt'] === null ? null : $result['publishedAt']->format(\DateTimeInterface::ATOM);

        return $result;
    }

    public function getByMultipleApiFilters(array $params): array
    {
        // TODO: Implement getByMultipleApiFilters() method.
    }
}
