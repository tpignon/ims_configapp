<?php

namespace AppBundle\Services;

//use Symfony\Component\HttpFoundation\StreamedResponse; // used for the export

class GsrmDataQualityChecks
{
    public function onVersionGeoStructureCode($importMappingRepository, $currentMappingRepository)
    {
        $dataQualityChecks = array();

        $distinctClientoutputidInImportMapping = $importMappingRepository->getDistinctClientOutputId();
        for ($row = 0; $row < count($distinctClientoutputidInImportMapping); $row++)
        {
            $currentClientoutputid = $distinctClientoutputidInImportMapping[$row]['clientOutputId']; // Current Client_output_id
            $distinctVersionGeoStructureCodeInImportMapping = $importMappingRepository->getDistinctVersionGeoStructureCode($currentClientoutputid);
            $distinctVersionGeoStructureCodeInCurrentMapping = $currentMappingRepository->getDistinctVersionGeoStructureCode($currentClientoutputid);

            // Check if version_geo_structure_code is unique
            if (count($distinctVersionGeoStructureCodeInImportMapping) > '1')
            {
                $dataQualityChecks = array(
                    'error_message' => 'Version_geo_structure_code must be unique for one ClientoutputId.',
                    'client_output_id' => $currentClientoutputid,
                    'distinct_version_geo_structure_code' => $distinctVersionGeoStructureCodeInImportMapping
                );
            }

            // Check if version_geo_structure_code has been changed
            if (count($distinctVersionGeoStructureCodeInCurrentMapping) != 0 and $distinctVersionGeoStructureCodeInImportMapping[0]['versionGeoStructureCode'] !== $distinctVersionGeoStructureCodeInCurrentMapping[0]['versionGeoStructureCode'])
            {
                $dataQualityChecks[] = array(
                    'client_output_id' => $currentClientoutputid,
                    'status' => 'WARNING',
                    'info' => 'Version_geo_structure_code will be changed by "' . $distinctVersionGeoStructureCodeInImportMapping[0]['versionGeoStructureCode'] . '" (instead of "' . $distinctVersionGeoStructureCodeInCurrentMapping[0]['versionGeoStructureCode'] . '").'
                );
            }
        }

        return $dataQualityChecks;
    }


    public function onMappings($importMappingRepository, $currentMappingRepository, $DwhDimGeoSalesRepRepository, $em)
    {
        $dataQualityChecks = array();
        $importMappingsArray = array(); // Will used to find the removed mappings
        $currentMappingsArray = array(); // Will used to find the removed mappings

        // --------------------------------------------------------------------------------------
        // Import mapping status
        // --------------------------------------------------------------------------------------
        // Loop on all import mappings
        $listImportMappingsArray = $importMappingRepository->findAll();
        foreach($listImportMappingsArray as $importMapping) {
            $importMappingClientoutputId = $importMapping->getClientOutputId();
            $importMappingGeoLevel = $importMapping->getGeoLevelNumber();
            $importMappingGeoValue = $importMapping->getGeoValue();
            $importMappingGeoTeam = $importMapping->getGeoTeam();
            $importMappingSalesRepFirstName = $importMapping->getSrFirstName();
            $importMappingSalesRepLastName = $importMapping->getSrLastName();

            // Status
            $statusUnexpectedMapping = $DwhDimGeoSalesRepRepository->getGeoValue($importMappingClientoutputId, $importMappingGeoLevel, $importMappingGeoValue);
            $statusUnchangedMapping = $currentMappingRepository->getUnchangedMapping($importMappingClientoutputId, $importMappingGeoLevel, $importMappingGeoValue, $importMappingGeoTeam, $importMappingSalesRepFirstName, $importMappingSalesRepLastName);
            $statusChangedMapping = $currentMappingRepository->getChangedMapping($importMappingClientoutputId, $importMappingGeoLevel, $importMappingGeoValue, $importMappingGeoTeam);
            if (count($statusUnexpectedMapping) == 0) { // Check if UNEXPECTED mapping
                $importMapping->setMappingStatus('UNEXPECTED');
                $em->persist($importMapping);
                $dataQualityChecks[] = array(
                    'client_output_id' => $importMappingClientoutputId,
                    'status' => 'WARNING',
                    'info' => 'Unexpected mapping on geo_level ' . $importMappingGeoLevel . ': geo "' . $importMappingGeoValue . '" doesn\'t currently exist in PharmaReport data warehouse.'
                );
            } elseif (count($statusUnchangedMapping) > 0) { // Check if UNCHANGED mapping
                $importMapping->setMappingStatus('UNCHANGED');
                $em->persist($importMapping);
            } elseif (count($statusChangedMapping) > 0) { // Check if CHANGED mapping
                $importMapping->setMappingStatus('CHANGED');
                $em->persist($importMapping);
                $currentMappingSalesRepFirstName = $statusChangedMapping[0]['srFirstName'];
                $currentMappingSalesRepLastName = $statusChangedMapping[0]['srLastName'];
                $dataQualityChecks[] = array(
                    'client_output_id' => $importMappingClientoutputId,
                    'status' => 'CHANGED MAPPING',
                    'info' => 'SalesRep will be changed for geo "' . $importMappingGeoValue . '" (level ' . $importMappingGeoLevel . ') and for team "' . $importMappingGeoTeam . '" ==> ' . $importMappingSalesRepFirstName . ' ' . $importMappingSalesRepLastName . ' (instead of ' . $currentMappingSalesRepFirstName . ' ' . $currentMappingSalesRepLastName . ').'
                );
            } else { // ELSE, it's a NEW mapping
                $importMapping->setMappingStatus('NEW');
                $em->persist($importMapping);
                $dataQualityChecks[] = array(
                    'client_output_id' => $importMappingClientoutputId,
                    'status' => 'NEW MAPPING',
                    'info' => 'New mapping for geo "' . $importMappingGeoValue . '" (level ' . $importMappingGeoLevel . ') ==> Team: ' . $importMappingGeoTeam . ', SalesRep: ' . $importMappingSalesRepFirstName . ' ' . $importMappingSalesRepLastName . '.'
                );
            }

            // Fill import mappings array which will be compared to current mappings to find the removed ones
            $importMappingsArray[] = array(
                'client_output_id' => $importMappingClientoutputId,
                'geo_level' => $importMappingGeoLevel,
                'geo_value' => $importMappingGeoValue,
                'geo_team' => $importMappingGeoTeam,
            );
        }


        // --------------------------------------------------------------------------------------
        // Missing & removed mappings
        // --------------------------------------------------------------------------------------
        $distinctClientoutputidInImportMapping = array();
        $distinctGeoLevelInImportMapping = array();
        $distinctGeoValueInImportMapping = array();
        $distinctGeoValueInDwh = array();
        // Distinct client_output_id in IMPORT
        $distinctClientoutputidInImportMapping = $importMappingRepository->getDistinctClientOutputId();
        for ($idRow = 0; $idRow < count($distinctClientoutputidInImportMapping); $idRow++)
        {
            $clientOutputId = $distinctClientoutputidInImportMapping[$idRow]['clientOutputId'];
            // --------------------------------------------------------------------------------------
            // Missing mappings
            // --------------------------------------------------------------------------------------
            // Distinct geo_level in IMPORT
            $distinctGeoLevelInImportMapping = $importMappingRepository->getDistinctGeoLevelNumber($clientOutputId);
            for ($levelRow = 0; $levelRow < count($distinctGeoLevelInImportMapping); $levelRow++)
            {
                $geoLevel = $distinctGeoLevelInImportMapping[$levelRow]['geoLevelNumber'];
                $dwhGeoLevelColumnName = 'geoLevel' . $geoLevel;
                // Distinct geo_value in IMPORT and DWH
                $distinctGeoValueInImportMapping = array_column($importMappingRepository->getDistinctGeoValue($clientOutputId, $geoLevel), 'geoValue');
                $distinctGeoValueInDwh = array_column($DwhDimGeoSalesRepRepository->getDistinctGeoValue($clientOutputId, $geoLevel), $dwhGeoLevelColumnName);

                $comparisonDwhWithImport = array_diff($distinctGeoValueInDwh, $distinctGeoValueInImportMapping);
                if (count($comparisonDwhWithImport) > 0)
                {
                    for ($row = 0; $row < count($comparisonDwhWithImport); $row++)
                    {
                        $geoValue = $comparisonDwhWithImport[$row];
                        $dataQualityChecks[] = array(
                            'client_output_id' => $clientOutputId,
                            'status' => 'WARNING',
                            'info' => 'There is currently no mapping for geo "' . $geoValue . '" on geo level ' . $geoLevel . '.'
                        );
                    }
                }
            }
            // --------------------------------------------------------------------------------------
            // Removed mappings
            // --------------------------------------------------------------------------------------
            // Current mappings array with client_output_id, geo_level, geo_value and geo_team
            $currentMappings = $currentMappingRepository->getMappingsToFindTheRemovedOnes($clientOutputId);
            for ($row = 0; $row < count($currentMappings); $row++) {
                $currentMappingGeoLevel = $currentMappings[$row]['geoLevelNumber'];
                $currentMappingGeoValue = $currentMappings[$row]['geoValue'];
                $currentMappingGeoTeam = $currentMappings[$row]['geoTeam'];
                $correspondingImportMapping = $importMappingRepository->getCorrespondingMapping($clientOutputId, $currentMappingGeoLevel, $currentMappingGeoValue, $currentMappingGeoTeam);
                if (count($correspondingImportMapping) == 0) {
                    $dataQualityChecks[] = array(
                        'client_output_id' => $clientOutputId,
                        'status' => 'REMOVED MAPPING',
                        'info' => 'Mapping for team "' . $currentMappingGeoTeam . '" and geo "' . $currentMappingGeoValue . '" on level ' . $currentMappingGeoLevel . ' will be removed.'
                    );
                }
            }
        }

        // --------------------------------------------------------------------------------------
        // Return of onMappings function
        // --------------------------------------------------------------------------------------
        return $dataQualityChecks;
    }


    public function multidimensional_array_diff($array1, $array2)
    {
        $result = array();



        foreach ($array2 as $key => $second)
        {
            foreach ($array1 as $key => $first)
            {
                foreach ($first as $first_value)
                {
                    foreach ($second as $second_value)
                    {
                        if ($first_value == $second_value)
                        {
                            $true = true;
                            break;
                        }
                    }
                    if (!isset($true))
                    {
                        $result[$key][] = $first_value;
                    }
                    unset($true);
                }
            }
        }
        // ------------------
        foreach ($array2 as $key => $second)
        {
            foreach ($array1 as $key => $first)
            {
                if (isset($array2[$key]))
                {
                    foreach ($first as $first_value)
                    {
                        foreach ($second as $second_value)
                        {
                            if ($first_value == $second_value)
                            {
                                $true = true;
                                break;
                            }
                        }
                        if (!isset($true))
                        {
                            $result[$key][] = $first_value;
                        }
                        unset($true);
                    }
                }
                else
                {
                    $result[$key] = $first;
                }
            }
        }
        return $result;
    }


    // ========================================================================================================
    // TESTS
    // ========================================================================================================

}
