<?php

namespace AppBundle\DataQualityChecks;

class DuplicateData
{
    // Criteria -> Key = column name & Value = column value
    public function isDuplicate($importRepository, $criteria)
    {
        $importData = $importRepository->findBy($criteria);
        if (count($importData) > 1) {
            return true;
        } else {
            return false;
        }
    }
}
