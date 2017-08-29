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

}
