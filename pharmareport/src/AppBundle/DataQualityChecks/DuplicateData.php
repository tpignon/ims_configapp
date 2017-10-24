<?php

namespace AppBundle\DataQualityChecks;

class DuplicateData
{
    /*
    public function getRemoved($fields, $currentRepository, $importRepository)
    {
        $removedData = array();
        $importClientoutputids = $importRepository->getDistinctClientOutputId();
        foreach($distinctClientoutputids as $clientoutputid)
        {


            $importValues = $importRepository->getDistinctValues($fields, $clientoutputid['clientOutputId']);
            foreach ($importValues as $importValue) {
                $isUnexpectedData = $this->isUnexpectedData($dwhRepository, $importValue);
                if ($isUnexpectedData) {
                    $unexpectedResults[] = $importValue;
                }
            }
        }
        return $unexpectedResults;
    }*/

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
