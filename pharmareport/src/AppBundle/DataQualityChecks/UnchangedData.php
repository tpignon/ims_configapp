<?php

namespace AppBundle\DataQualityChecks;

//use AppBundle\Services\MultidimensionalArraysDiff;

class UnchangedData
{
    // Criteria -> Key = column name & Value = column value
    public function isUnchanged($currentRepository, $criteria)
    {
        // Si existe dans dwh ET que targetUnits, targetValue, MSTargetUnits , .. est le même, alors aucun changement
        // Autrement dit, si toutes les colonnes sont identiques, pas de changement
        $currentData = $currentRepository->findOneBy($criteria);
        if (count($currentData) == 0) {
            return false;
        } else {
            return true;
        }
    }
}
