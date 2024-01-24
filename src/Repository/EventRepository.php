<?php

namespace App\Repository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\User;

use App\Entity\Event;
use App\Entity\MonitorizableEvent;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findAllFromToQB($criteriaAnd = null, $criteriaLike = null, $from = null, $to = null)
    {
        $qb = $this->createQueryBuilder('e');
        if (null !== $from) {
            $qb->andWhere('e.date >= :from')
                ->setParameter('from', $from);
        }
        if (null !== $to) {
            $qb->andWhere('e.date <= :to')
                ->setParameter('to', $to);
        }
        if ($criteriaAnd) {
            foreach ($criteriaAnd as $eremua => $filtroa) {
                if (null !== $filtroa) {
                    $qb->andWhere('e.' . $eremua . ' = :' . $eremua)
                        ->setParameter($eremua, $filtroa);
                } else {
                    $qb->andWhere('e.' . $eremua . ' is null');
                }
            }
        }
        if ($criteriaLike) {
            foreach ($criteriaLike as $eremua => $filtroa) {
                $qb->andWhere('e.' . $eremua . ' LIKE :' . $eremua)
                    ->setParameter($eremua, '%' . $filtroa . '%');
            }
        }
        $qb->orderBy('e.date', 'DESC');

        return $qb;
    }

    public function findAllFromTo($criteria = null, $from = null, $to = null)
    {
        $criteriaLikeKeys = ['subject' => null, 'details' => null];
        $criteriaLike = $criteriaAnd = null;
        if (null !== $criteria) {
            $criteriaLike = array_intersect_key($criteria, $criteriaLikeKeys);
            $criteriaAnd = array_diff_key($criteria, $criteriaLikeKeys);
        }

        return $this->findAllFromToQB($criteriaAnd, $criteriaLike, $from, $to)->getQuery()->getResult();
    }

    public function findUnmatchedEventsFromTo($criteria = null, $from = null, $to = null)
    {
        $criteria['monitorizableEvent'] = null;

        return $this->findAllFromTo($criteria, $from, $to);
    }

    /**
     * @return Event|null Return the lastMatchedEvent if there is one.
     */
    public function findLastMatchedEvent(MonitorizableEvent $mevent)
    {
        $criteria['monitorizableEvent'] = $mevent;

        return $this->findOneBy($criteria, ['date' => 'DESC']);
    }
}
