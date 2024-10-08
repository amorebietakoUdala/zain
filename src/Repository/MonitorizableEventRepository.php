<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\User;

use App\Entity\MonitorizableEvent;

/**
 * @method MonitorizableEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method MonitorizableEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method MonitorizableEvent[]    findAll()
 * @method MonitorizableEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MonitorizableEventRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, MonitorizableEvent::class);
   }
   
}
