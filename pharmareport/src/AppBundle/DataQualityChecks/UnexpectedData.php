<?php

namespace AppBundle\DataQualityChecks;

class UnexpectedData
{
    // Fields as parameter are the column names
    public function getUnexpected($fields, $importRepository, $dwhRepository)
    {
        $unexpectedResults = array();
        $distinctClientoutputids = $importRepository->getDistinctClientOutputId();
        foreach($distinctClientoutputids as $clientoutputid)
        {
            $importValues = $importRepository->getDistinctValues($fields, $clientoutputid['clientOutputId']);
            foreach ($importValues as $importValue) {
                $isUnexpectedData = $this->isUnexpected($dwhRepository, $importValue);
                if ($isUnexpectedData) {
                    $unexpectedResults[] = $importValue;
                }
            }
        }
        return $unexpectedResults;
    }

    // Criteria -> Key = column name & Value = column value
    public function isUnexpected($dwhRepository, $criteria)
    {
        $dwhValue = $dwhRepository->findOneBy($criteria);
        if (count($dwhValue) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
