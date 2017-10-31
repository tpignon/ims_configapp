<?php

namespace AppBundle\DataQualityChecks;

//use AppBundle\Services\MultidimensionalArraysDiff;

class RemovedData
{
    // Criteria -> Key = column name & Value = column value
    public function isRemoved($importRepository, $criteria)
    {
        $importData = $importRepository->findOneBy($criteria);
        if (count($importData) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
