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


    public function getDistinctGeoValue($clientOutputId, $geoLevel)
    {
        $qb = $this->createQueryBuilder('d');

        $qb
          ->select('d.geoLevel' . $geoLevel)
          ->where('d.clientOutputId = :id')
          ->setParameter('id', $clientOutputId)
          ->distinct()
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }


    public function getGeoValue($clientOutputId, $geoLevel, $geoValue)
    {
        $qb = $this->createQueryBuilder('d');

        $qb
          ->select('d.geoLevel' . $geoLevel)
          ->where('d.clientOutputId = :id')
          ->setParameter('id', $clientOutputId)
          ->andWhere('d.geoLevel' . $geoLevel . ' = :value')
          ->setParameter('value', $geoValue)
          ->distinct()
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }
}
