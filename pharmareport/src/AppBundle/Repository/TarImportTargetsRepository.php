<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TarImportTargetsRepository extends EntityRepository
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


    public function getDistinctValues($columns, $clientoutputid)
    {
        $alias = 'i';
        $qb = $this->createQueryBuilder($alias);

        foreach ($columns as &$columnName) {
            $columnName = $alias . '.' . $columnName;
        }

        $qb
          ->select($columns)
          ->where($alias . '.clientOutputId = :id')
          ->setParameter('id', $clientoutputid)
          ->distinct()
        ;

        return $qb
          ->getQuery()
          ->getArrayResult()
        ;
    }

}
