<?php

namespace AppBundle\DataQualityChecks;

//use AppBundle\Services\MultidimensionalArraysDiff;

class RemovedData
{
    /*private $multidimensionalArraysDiff;

    public function __construct(MultidimensionalArraysDiff $multidimensionalArraysDiff)
    {
        $this->multidimensionalArraysDiff = $multidimensionalArraysDiff;
    }*/

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
