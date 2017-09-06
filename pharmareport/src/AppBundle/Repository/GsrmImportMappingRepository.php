<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class GsrmImportMappingRepository extends EntityRepository
{

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

    public function getDistinctGeoLevelNumber($clientoutputid)
    {
        $qb = $this->createQueryBuilder('i');

        $qb
          ->select('i.geoLevelNumber')
          ->where('i.clientOutputId = :id')
          ->setParameter('id', $clientoutputid)
          ->distinct()
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }

    public function getDistinctGeoValue($clientoutputid, $geoLevelNumber)
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


    public function getCorrespondingMapping($clientOutputId, $geoLevel, $geoValue, $geoTeam)
    {
        $qb = $this->createQueryBuilder('i');

        $qb
          ->select('i.ptk, i.clientOutputId, i.srFirstName, i.srLastName')
          ->where('i.clientOutputId = :id')
          ->setParameter('id', $clientOutputId)
          ->andWhere('i.geoLevelNumber = :level')
          ->setParameter('level', $geoLevel)
          ->andWhere('i.geoValue = :value')
          ->setParameter('value', $geoValue)
          ->andWhere('i.geoTeam = :team')
          ->setParameter('team', $geoTeam)
          ->distinct()
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }


    public function getNbrOfMappings($clientOutputId)
    {
        $qb = $this->createQueryBuilder('i');

        $qb
          ->select('count(i.ptk)')
          ->where('i.clientOutputId = :id')
          ->setParameter('id', $clientOutputId)
        ;

        return $qb
          ->getQuery()
          ->getSingleScalarResult()
        ;
    }


    public function getNbrOfChangedMappings($clientOutputId)
    {
        $qb = $this->createQueryBuilder('i');

        $qb
          ->select('count(i.mappingStatus)')
          ->where('i.clientOutputId = :id')
          ->setParameter('id', $clientOutputId)
          ->andwhere('i.mappingStatus = :status')
          ->setParameter('status', 'CHANGED')
        ;

        return $qb
          ->getQuery()
          ->getSingleScalarResult()
        ;
    }


    public function getNbrOfNewMappings($clientOutputId)
    {
        $qb = $this->createQueryBuilder('i');

        $qb
          ->select('count(i.mappingStatus)')
          ->where('i.clientOutputId = :id')
          ->setParameter('id', $clientOutputId)
          ->andwhere('i.mappingStatus = :status')
          ->setParameter('status', 'NEW')
        ;

        return $qb
          ->getQuery()
          ->getSingleScalarResult()
        ;
    }


    public function getNbrOfUnchangedMappings($clientOutputId)
    {
        $qb = $this->createQueryBuilder('i');

        $qb
          ->select('count(i.mappingStatus)')
          ->where('i.clientOutputId = :id')
          ->setParameter('id', $clientOutputId)
          ->andwhere('i.mappingStatus = :status')
          ->setParameter('status', 'UNCHANGED')
        ;

        return $qb
          ->getQuery()
          ->getSingleScalarResult()
        ;
    }
}
