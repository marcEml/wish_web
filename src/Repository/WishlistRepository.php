<?php

namespace App\Repository;

use App\Entity\Wishlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wishlist>
 */
class WishlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }

    public function findTop3ListsByValue(): array
    {
        return $this->createQueryBuilder('w')

            ->select('w as wishlist, SUM(i.price) as totalValue')
            ->leftJoin('w.items', 'i')
            ->groupBy('w.id')
            ->orderBy('totalValue', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getArrayResult();
    }

    public function findWithItems($id): ?Wishlist
    {
        return $this->createQueryBuilder('w')
            ->leftJoin('w.items', 'i')
            ->addSelect('i')
            ->where('w.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }


}
