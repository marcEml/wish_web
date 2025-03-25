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

        return $this->createQueryBuilder('i')
            ->leftJoin('i.wishlist', 'w')
            ->where('w.id = :wid')
            ->setParameter('wid', $wishlist->getId())
            ->orderBy('i.price', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }


}
