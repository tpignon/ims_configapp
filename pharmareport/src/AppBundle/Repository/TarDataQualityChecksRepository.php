<?php

namespace AppBundle\Repository;

/**
 * TarDataQualityChecksRepository
 */
class TarDataQualityChecksRepository extends \Doctrine\ORM\EntityRepository
{
    public function getDistinctClientOutputId($loadDate)
    {
        $qb = $this->createQueryBuilder('d');

        $qb
          ->select('d.clientOutputId')
          ->where('d.loadDate = :loadDateParameter')
          ->setParameter('loadDateParameter', $loadDate)
          ->distinct()
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }
}
