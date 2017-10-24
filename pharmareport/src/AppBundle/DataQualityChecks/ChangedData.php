<?php

namespace AppBundle\DataQualityChecks;

//use AppBundle\Services\MultidimensionalArraysDiff;

class ChangedData
{
    // Criteria -> Key = column name & Value = column value
    public function isChanged($currentRepository, $fixedComparativeCriteria, $changeableCriteria)
    {
        // Si existe dans dwh ET que targetUnits ou targetValue ou MSTargetUnits ou .. est differente, alors c'est un changement
        $currentData = $currentRepository->findOneBy($fixedComparativeCriteria);
        if (count($currentData) == 0) {
            return false;
        } else {
            $allCriteria = $fixedComparativeCriteria;
            foreach ($changeableCriteria as $criteriaKey => $criteriaValue) {
                $allCriteria[$criteriaKey] = $criteriaValue;
            }
            $currentData = $currentRepository->findOneBy($allCriteria);
            if (count($currentData) == 0) {
                return true;
            } else {
                return false;
            }
        }
    }
}
