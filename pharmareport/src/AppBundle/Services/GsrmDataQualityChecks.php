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

        // --------------------------------------------------------------------------------------
        // Import mapping status
        // --------------------------------------------------------------------------------------
        // Extract import mappings into array
        $listImportMappingsArray = $importMappingRepository->findAll();

        // Loop on all import mappings
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
        }


        // --------------------------------------------------------------------------------------
        // Current mapping status (to see which mapping will be removed)
        // --------------------------------------------------------------------------------------
        // Extract into array all current mappings which are different from import mapping
        // Loop on all these above current mappings
        // Extract all similar import mappings into array
        // Get status of this analyzed current mapping
        // Insert only "will be removed" result into data quality checks array


        // --------------------------------------------------------------------------------------
        // Comparison with DWH_d_geo_sales_rep (to see unexpected & missing mappings)
        // --------------------------------------------------------------------------------------
        // Check for unexpected mappings
        // Check for missing mappings

        return $dataQualityChecks;
    }


    // ========================================================================================================
    // TESTS
    // ========================================================================================================
    public function __construct($templating)
    {
        $this->templating = $templating;
    }

    public function testMessage()
    {
        $messsage = $this->getMyMessage();
        return $messsage;
    }

    public function getMyMessage()
    {
        $messsage = "Hé mon ami!";
        return $messsage;
    }

    public function dataQualityCheckOnVersionGeoStructureCode($clientOutputId, $importMappingRepository, $currentMappingRepository)
    {
        $distinctVersionGeoStructureCodeInImportMapping = $importMappingRepository->getDistinctVersionGeoStructureCode($currentClientoutputid);
        $distinctVersionGeoStructureCodeInCurrentMapping = $currentMappingRepository->getDistinctVersionGeoStructureCode($currentClientoutputid);

        if (count($distinctVersionGeoStructureCodeInImportMapping) > '1')
        {
            return $this->templating->render('GSRM/error_version_geo_structure_code.html.twig', array(
                'error_message' => 'Version_geo_structure_code must be unique for one ClientoutputId. ',
                'client_output_id' => $currentClientoutputid,
                'distinct_version_geo_structure_code' => $distinctVersionGeoStructureCodeInImportMapping
            ));
        }

        if (count($distinctVersionGeoStructureCodeInCurrentMapping) == 0)
        {
            $firstMappingBoolean = true;
        } else
        {
            $firstMappingBoolean = false;
        }

        return $firstMappingBoolean;
    }

    public function dataQualityCheckOnGeoTeam($clientOutputId, $importMappingRepository, $currentMappingRepository)
    {


        // ---------------------------------------
        // 2 - geoTeam
        // ---------------------------------------
        $distinctGeoTeamInImportMapping = $importMappingRepository->getDistinctValuesInColumn('geoTeam', $currentClientoutputid);
        $distinctGeoTeamInCurrentMapping = $currentMappingRepository->getDistinctValuesInColumn('geoTeam', $currentClientoutputid);

        $importArray = array(); // Store value in array to be compared
        for ($i = 0; $i < count($distinctGeoTeamInImportMapping); $i++) {
            $importArray[] = $distinctGeoTeamInImportMapping[$i]['geoTeam'];
        }

        $currentArray = array(); // Store value in array to be compared
        for ($u = 0; $u < count($distinctGeoTeamInCurrentMapping); $u++) {
            $currentArray[] = $distinctGeoTeamInCurrentMapping[$u]['geoTeam'];
        }

        $comparisonFromImport = array_diff($importArray, $currentArray);
        $comparisonFromCurrent = array_diff($currentArray, $importArray);

        // Check for New and Removed values
        if (count($comparisonFromImport) > '0') {
            foreach ($comparisonFromImport as $item) {
                $dataQualityCheck = new GsrmDataQualityChecks();
                $dataQualityCheck->setClientOutputId($currentClientoutputid);
                $dataQualityCheck->setLoadDate($currentLoadDate);
                $dataQualityCheck->setAnalyzedField('geo_team');
                $dataQualityCheck->setStatus('WARNING');
                $dataQualityCheck->setInfo('New team (MarketId) "' . $item . '".');
                $em->persist($dataQualityCheck);
            }
        } elseif (count($comparisonFromCurrent) > '0') {
            foreach ($comparisonFromCurrent as $item) {
                $dataQualityCheck = new GsrmDataQualityChecks();
                $dataQualityCheck->setClientOutputId($currentClientoutputid);
                $dataQualityCheck->setLoadDate($currentLoadDate);
                $dataQualityCheck->setAnalyzedField('geo_team');
                $dataQualityCheck->setStatus('WARNING');
                $dataQualityCheck->setInfo('Team (MarketId) "' . $item . '" will be removed.');
                $em->persist($dataQualityCheck);
            }
        } else {
            $dataQualityCheck = new GsrmDataQualityChecks();
            $dataQualityCheck->setClientOutputId($currentClientoutputid);
            $dataQualityCheck->setLoadDate($currentLoadDate);
            $dataQualityCheck->setAnalyzedField('geo_team');
            $dataQualityCheck->setStatus('CORRECT');
            $dataQualityCheck->setInfo('No change.');
        }

        $em->persist($dataQualityCheck);
    }
}
