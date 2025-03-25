<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Item>
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function findTop3ByWishlist($wishlist): array
    {
    // 如果是多对多(一个wishlist包含多个items, item也可在多个wishlist里)
    // 假设你的实体里: Item->wishlists is a ManyToMany
        return $this->createQueryBuilder('i')
            ->leftJoin('i.wishlists', 'w')
            ->where('w.id = :wid')
            ->setParameter('wid', $wishlist->getId())
            ->orderBy('i.price', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Item[] Returns an array of Item objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Item
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
