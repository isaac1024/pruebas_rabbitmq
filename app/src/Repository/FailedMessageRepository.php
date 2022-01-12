<?php

namespace App\Repository;

use App\Entity\FailedMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FailedMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method FailedMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method FailedMessage[]    findAll()
 * @method FailedMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FailedMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FailedMessage::class);
    }

    // /**
    //  * @return FailedMessage[] Returns an array of FailedMessage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FailedMessage
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
