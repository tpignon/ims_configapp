<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DwhDimGeoSalesRepRepository extends EntityRepository
{

    public function getDistinctValuesInColumn($columnKey, $clientoutputid)
    {
        $qb = $this->createQueryBuilder('d');

        $qb
          ->select('d.' . $columnKey)
          ->where('d.clientOutputId = :id')
          ->setParameter('id', $clientoutputid)
          ->distinct()
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }
}
