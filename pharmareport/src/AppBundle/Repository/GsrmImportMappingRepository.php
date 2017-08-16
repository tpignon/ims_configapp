<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class GsrmImportMappingRepository extends EntityRepository
{

    public function getDistinctClientOutputId()
    {
        $qb = $this->createQueryBuilder('i');

        $qb
          ->select('i.clientOutputId')
          ->distinct()
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }

    public function getDistinctValuesInColumn($columnKey, $clientoutputid)
    {
        $qb = $this->createQueryBuilder('i');

        $qb
          //->select('i.clientOutputId')
          ->select('i.' . $columnKey)
          ->where('i.clientOutputId = :id')
          ->setParameter('id', $clientoutputid)
          ->distinct()
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }

    public function getDistinctVersionGeoStructureCode($clientoutputid)
    {
        $qb = $this->createQueryBuilder('i');

        $qb
          ->select('i.versionGeoStructureCode')
          ->where('i.clientOutputId = :id')
          ->setParameter('id', $clientoutputid)
          ->distinct()
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }

    public function getDistinctSalesRep($clientoutputid)
    {
        $qb = $this->createQueryBuilder('i');

        $qb
          ->select('i.srFirstName, i.srLastName')
          ->where('i.clientOutputId = :id')
          ->setParameter('id', $clientoutputid)
          ->distinct()
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }

    public function getDistinctGeoName($clientoutputid, $geoLevelNumber)
    {
        $qb = $this->createQueryBuilder('i');

        $qb
          ->select('i.geoValue')
          ->where('i.clientOutputId = :id')
          ->setParameter('id', $clientoutputid)
          ->andWhere('i.geoLevelNumber = :level')
          ->setParameter('level', $geoLevelNumber)
          ->distinct()
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }
}
