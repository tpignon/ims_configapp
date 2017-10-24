<?php

namespace AppBundle\DataQualityChecks;

//use AppBundle\Services\MultidimensionalArraysDiff;

class NewData
{
    // Criteria -> Key = column name & Value = column value
    public function isNew($currentRepository, $criteria)
    {
        $currentValue = $currentRepository->findOneBy($criteria);
        if (count($currentValue) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
