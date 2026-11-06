<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Topic>
 */
class TopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Topic::class);
    }

    /**
     * @return Topic[] Returns an array of Topic objects
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.title LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Topic[] Returns an array of Topic objects
     */
    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.category = :category')
            ->setParameter('category', $category)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Topic[] Returns an array of Topic objects
     */
    public function LastByCreatedAt(?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->join('t.category', 'c')
            ->join('t.author', 'u')
            ->select('t', 'c', 'u')
            ->orderBy('t.createdAt', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Most popular topics over the last $days days, ordered by the column
     * chosen by the user (score = sum of the votes on the topic's comments).
     *
     * @return array<int, array<string, mixed>>
     */
    public function findPopular(int $days, string $sort): array
    {
        return $this->createQueryBuilder('t')
            ->select(
                't.id',
                't.title',
                't.content',
                't.createdAt AS created_at',
                'u.nickname AS author',
                'ca.id AS category_id',
                'ca.name AS category',
                'COUNT(DISTINCT c.id) AS comment_count',
                'COALESCE(SUM(v.value), 0) AS score',
            )
            ->join('t.author', 'u')
            ->join('t.category', 'ca')
            ->leftJoin('t.comments', 'c')
            ->leftJoin('c.votes', 'v')
            ->andWhere('t.createdAt >= :since')
            ->setParameter('since', new \DateTime(sprintf('-%d days', max(0, $days))))
            ->groupBy('t.id')
            // the column to sort on comes straight from the ?tri= query parameter
            ->orderBy($sort, 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getArrayResult();
    }

}
