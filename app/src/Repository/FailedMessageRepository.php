<?php

namespace App\Repository;

use App\Entity\FailedMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FailedMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FailedMessage::class);
    }

    public function save(FailedMessage $failedMessage): void
    {
        $this->_em->persist($failedMessage);
        $this->_em->flush();
    }

    public function delete(FailedMessage $failedMessage): void
    {
        $this->_em->remove($failedMessage);
        $this->_em->flush();
    }
}
