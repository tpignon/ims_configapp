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

            // Check if version_geo_structure_code column has only one value by datasetID
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
        $importMappingsArray = array(); // Will be used to find the removed mappings
        $currentMappingsArray = array(); // Will be used to find the removed mappings

        // --------------------------------------------------------------------------------------
        // Import mapping status
        // --------------------------------------------------------------------------------------
        // Loop on all import mappings
        $listImportMappingsArray = $importMappingRepository->findAll();
        foreach($listImportMappingsArray as $importMapping)
        {
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
        $clientOutputIdArray = array_column($importMappingsArray, 'client_output_id');
        $distinctClientoutputidArray = array_unique($clientOutputIdArray);
        foreach ($distinctClientoutputidArray as $dsID)
        {
            // --------------------------------------------------------------------------------------
            // Missing mappings
            // --------------------------------------------------------------------------------------
            // Distinct geo_level in IMPORT
            $distinctGeoLevelInImportMapping = $importMappingRepository->getDistinctGeoLevelNumber($dsID);
            for ($levelRow = 0; $levelRow < count($distinctGeoLevelInImportMapping); $levelRow++)
            {
                $geoLevel = $distinctGeoLevelInImportMapping[$levelRow]['geoLevelNumber'];
                $distinctGeoValueInImportMapping = $importMappingRepository->getDistinctGeoValue($dsID, $geoLevel);
                $distinctGeoValueInDWH = $DwhDimGeoSalesRepRepository->getDistinctGeoValue($dsID, 'geoLevel' . $geoLevel);

                $importGeoValueArray = array(); // Store value in array to be compared
                for ($i = 0; $i < count($distinctGeoValueInImportMapping); $i++) {
                    $importGeoValueArray[] = $distinctGeoValueInImportMapping[$i]['geoValue'];
                }
                $DwhGeoValueArray = array(); // Store value in array to be compared
                for ($u = 0; $u < count($distinctGeoValueInDWH); $u++) {
                    $DwhGeoValueArray[] = $distinctGeoValueInDWH[$u]['geoLevel'.$geoLevel];
                }

                $comparisonDwhWithImport = array_diff($DwhGeoValueArray, $importGeoValueArray);

                if (count($comparisonDwhWithImport) > '0') {
                    foreach ($comparisonDwhWithImport as $geoValue) {
                        $dataQualityChecks[] = array(
                            'client_output_id' => $dsID,
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
            $currentMappings = $currentMappingRepository->getMappingsToFindTheRemovedOnes($dsID);
            for ($row = 0; $row < count($currentMappings); $row++) {
                $currentMappingGeoLevel = $currentMappings[$row]['geoLevelNumber'];
                $currentMappingGeoValue = $currentMappings[$row]['geoValue'];
                $currentMappingGeoTeam = $currentMappings[$row]['geoTeam'];
                $correspondingImportMapping = $importMappingRepository->getCorrespondingMapping($dsID, $currentMappingGeoLevel, $currentMappingGeoValue, $currentMappingGeoTeam);
                if (count($correspondingImportMapping) == 0) {
                    $dataQualityChecks[] = array(
                        'client_output_id' => $dsID,
                        'status' => 'REMOVED MAPPING',
                        'info' => 'Mapping for team "' . $currentMappingGeoTeam . '" and geo "' . $currentMappingGeoValue . '" on level ' . $currentMappingGeoLevel . ' will be removed.'
                    );
                }
            }

            // --------------------------------------------------------------------------------------
            // Empty $dataQualityChecks array
            // At this step, this means we have no change in import mapping for this client_output_id:
            //    - No UNEXPECTED mapping
            //    - No CHANGED mapping
            //    - No NEW mapping
            //    - No MISSING mapping
            //    - No REMOVED mapping
            // --------------------------------------------------------------------------------------
            $nbrOfDataQualityChecksInArray = 0;
            $clientOutputIdInDataQualityChecksArray = array_column($dataQualityChecks, 'client_output_id');
            foreach ($clientOutputIdInDataQualityChecksArray as $DQC_ClientOutputId) {
                if ($DQC_ClientOutputId == $dsID) {
                    $nbrOfDataQualityChecksInArray++;
                }
            }
            if ($nbrOfDataQualityChecksInArray == 0) {
                $dataQualityChecks[] = array(
                    'client_output_id' => $dsID,
                    'status' => 'NO CHANGE',
                    'info' => 'No change in import mappings for this client_output_id ' . $dsID . '.'
                );
            }
        }

        // --------------------------------------------------------------------------------------
        // Return of onMappings function
        // --------------------------------------------------------------------------------------
        return $dataQualityChecks;
    }


    // ========================================================================================================
    // TESTS
    // ========================================================================================================

}
