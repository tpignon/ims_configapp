<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class GsrmCurrentMappingRepository extends EntityRepository
{

    public function getNumberOfMappings($clientoutputid)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select('count(c.ptk)')
            ->where('c.clientOutputId = :id')
            ->setParameter('id', $clientoutputid)
        ;

        return $qb
          ->getQuery()
          ->getSingleScalarResult()
        ;
    }


    public function getMappingByClientOutputId($clientoutputid)
    {
        $qb = $this->createQueryBuilder('g');

        $qb->where('g.clientOutputId = :clientOutputId')
              ->setParameter('clientOutputId', $clientoutputid)
        ;

        return $qb
          ->getQuery()
          ->getResult()
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
          //->select('i.clientOutputId')
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
          //->select('i.clientOutputId')
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


    public function getUnchangedMapping($clientOutputId, $geoLevel, $geoValue, $geoTeam, $srFirstName, $srLastName)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
          ->select('c.ptk, c.clientOutputId')
          ->where('c.clientOutputId = :id')
          ->setParameter('id', $clientOutputId)
          ->andWhere('c.geoLevelNumber = :level')
          ->setParameter('level', $geoLevel)
          ->andWhere('c.geoValue = :value')
          ->setParameter('value', $geoValue)
          ->andWhere('c.geoTeam = :team')
          ->setParameter('team', $geoTeam)
          ->andWhere('c.srFirstName = :firstName')
          ->setParameter('firstName', $srFirstName)
          ->andWhere('c.srLastName = :lastName')
          ->setParameter('lastName', $srLastName)
          ->distinct()
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }


    public function getChangedMapping($clientOutputId, $geoLevel, $geoValue, $geoTeam)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
          ->select('c.ptk, c.clientOutputId, c.srFirstName, c.srLastName')
          ->where('c.clientOutputId = :id')
          ->setParameter('id', $clientOutputId)
          ->andWhere('c.geoLevelNumber = :level')
          ->setParameter('level', $geoLevel)
          ->andWhere('c.geoValue = :value')
          ->setParameter('value', $geoValue)
          ->andWhere('c.geoTeam = :team')
          ->setParameter('team', $geoTeam)
          ->distinct()
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }


    public function getMappingsToFindTheRemovedOnes($clientOutputId)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
          ->select('c.geoLevelNumber, c.geoValue, c.geoTeam')
          ->where('c.clientOutputId = :id')
          ->setParameter('id', $clientOutputId)
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }
}
