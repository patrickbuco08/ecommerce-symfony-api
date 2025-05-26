<?php

namespace Bocum\Repository;

use Bocum\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    /**
     * Search products by name or description (case-insensitive, partial match).
     *
     * @param string $query
     * @return Product[]
     */
    public function searchByNameOrDescription(string $query): array
    {
        $qb = $this->getSearchByNameOrDescriptionQueryBuilder($query);
        return $qb->getQuery()->getResult();
    }

    /**
     * Returns a QueryBuilder for searching products by name or description (case sensitive).
     */
    public function getSearchByNameOrDescriptionQueryBuilder(string $query)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->like('p.title', ':query'),
                $qb->expr()->like('p.description', ':query')
            )
        )
        ->setParameter('query', '%' . $query . '%')
        ->orderBy('p.title', 'ASC');
        return $qb;
    }

    /**
     * Returns a QueryBuilder for all products (for pagination).
     */
    public function getAllProductsQueryBuilder()
    {
        return $this->createQueryBuilder('p')->orderBy('p.title', 'ASC');
    }
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
