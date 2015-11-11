<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

class SubscriptionStatusChangeRepository extends EntityRepository
{
    public function countByDateRange(DateTime $fromDate, DateTime $toDate)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(array('COUNT(s.id) AS total', 's.fromStatus', 's.toStatus'))
            ->from('AppBundle:SubscriptionStatusChange', 's')
            ->where('s.createdAt >= :start')
            ->andWhere('s.createdAt <= :end')
            ->setParameters(array('start' => $fromDate, 'end' => $toDate))
            ->groupBy('s.fromStatus, s.toStatus');

        $query =  $qb->getQuery();
        $results = $query->getArrayResult();

        return $results;
    }

    public function save(SubscriptionStatusChange $entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }
}
