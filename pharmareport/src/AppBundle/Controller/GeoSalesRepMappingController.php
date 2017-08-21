<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template; // used for the export
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse; // used for the export
use AppBundle\Entity\GsrmCurrentMapping;
use AppBundle\Entity\GsrmImportMapping;
use AppBundle\Entity\GsrmImportMappingFile;
use AppBundle\Entity\GsrmExportMappingTemplateFile;
use AppBundle\Entity\GsrmViewCurrentMapping;
use AppBundle\Entity\GeoSalesRepDataQualityChecks;
use AppBundle\Entity\GsrmDataQualityChecks;
use AppBundle\Entity\DwhDimGeoSalesRep;
use AppBundle\Form\GsrmImportMappingFileType;
use AppBundle\Form\GsrmExportMappingTemplateFileType;
use AppBundle\Form\GsrmViewCurrentMappingType;

class GeoSalesRepMappingController extends Controller
{

    /**
     * @Route("/gsrm", name="gsrm_index")
     */
    public function indexAction(Request $request)
    {

        // ================================================================================================================
        // Import file
        // ================================================================================================================

        $importGeoSalesRepFile = new GsrmImportMappingFile();
        $importGeoSalesRepForm = $this->createForm(GsrmImportMappingFileType::class, $importGeoSalesRepFile);

        if ($request->isMethod('POST') && $importGeoSalesRepForm->handleRequest($request)->isValid())
        {
            // --------------------------------------------------------------
            // Import mappings into an array
            // --------------------------------------------------------------
            $currentLoadDate = date('Y-m-d H:i:s');

            $GeoSalesRepMappingFileFolder = $this->getParameter('geosalesrep_csvfile_folder');
            $GeoSalesRepMappingFileName = $this->getParameter('geosalesrep_csvfile_filename');
            $GeoSalesRepMappingFile = $GeoSalesRepMappingFileFolder . '/' . $GeoSalesRepMappingFileName;
            $file = $importGeoSalesRepFile->getGsrmImportMappingFile();
            $file->move($GeoSalesRepMappingFileFolder, $GeoSalesRepMappingFileName);

            $geosalesrepImport = $this->get('GsrmImportMapping');
            $geosalesrepMappings = array(); // This array will contain elements extracted from csv file
            $geosalesrepMappings = $geosalesrepImport->importCSV($GeoSalesRepMappingFile); // Row 0 contains headers
            if (array_key_exists('error_type', $geosalesrepMappings))
            {
                // File doesn't exist
                if ($geosalesrepMappings['error_type'] == 'file_does_not_exist')
                {
                    return $this->render('GSRM/error_submit_file.html.twig', array(
                        'error_message' => 'File "' . $geosalesrepMappings['error_file'] . '" doesn\'t exist.',
                    ));
                }
                // Bad number of columns
                if ($geosalesrepMappings['error_type'] == 'nbr_items_on_row')
                {
                    return $this->render('GSRM/error_submit_file.html.twig', array(
                        'error_row' => $geosalesrepMappings['error_row'],
                        'error_message' => $geosalesrepMappings['error_nbr_of_columns'] . ' items found --> ' . $geosalesrepMappings['max_nbr_of_columns'] . ' items (columns) are expected.',
                    ));
                }
            }

            $em = $this->getDoctrine()->getManager();

            // --------------------------------------------------------------
            // Insert mappings into MySQL DB, table "gsrm_import_mapping"
            // --------------------------------------------------------------
            for ($row = 1; $row < count($geosalesrepMappings); $row++) // Start at 1 because the first row contains headers
            {
                $importGeoSalesRep = new GsrmImportMapping();
                $importGeoSalesRep->setClientOutputId($geosalesrepMappings[$row]['client_output_id']);
                $importGeoSalesRep->setVersionGeoStructureCode($geosalesrepMappings[$row]['version_geo_structure_code']);
                $importGeoSalesRep->setGeoTeam($geosalesrepMappings[$row]['geo_team']);
                $importGeoSalesRep->setGeoLevelNumber($geosalesrepMappings[$row]['geo_level_number']);
                $importGeoSalesRep->setGeoValue($geosalesrepMappings[$row]['geo_value']);
                $importGeoSalesRep->setSrFirstName($geosalesrepMappings[$row]['sr_first_name']);
                $importGeoSalesRep->setSrLastName($geosalesrepMappings[$row]['sr_last_name']);
                $importGeoSalesRep->setSrEmail($geosalesrepMappings[$row]['sr_email']);

                $validator = $this->get('validator');
                $errors = $validator->validate($importGeoSalesRep);
                $error_row = $row+1;

                if(count($errors) > 0)
                {
                    $error_message = array();
                    foreach ($errors as $error)
                    {
                        $error_message[] = $error->getMessage();
                    }
                    return $this->render('GSRM/error_asserts.html.twig', array(
                        'error_row' => $error_row,
                        'error_message' => $error_message,
                    ));
                }
                else
                {
                    $em->persist($importGeoSalesRep);
                }

            }

            // Truncate table "gsrm_import_mapping"
            $connection = $em->getConnection();
            $platform = $connection->getDatabasePlatform();
            $connection->executeUpdate($platform->getTruncateTableSQL('gsrm_import_mapping', true));
            $em->flush();

            // --------------------------------------------------------------
            // Data quality checks (DQC)
            // --------------------------------------------------------------
            /*
            $importMappingRepository = $em->getRepository('AppBundle:GsrmImportMapping');
            $currentMappingRepository = $em->getRepository('AppBundle:GsrmCurrentMapping');
            $DwhDimGeoSalesRepRepository = $em->getRepository('AppBundle:DwhDimGeoSalesRep');
            $dataQualityChecksService = $this->get('GsrmDataQualityChecks');
            */
            //$dataQualityChecks = array();

            // DQC on version_geo_structure_code
            /*
            $DQCOnVersionGeoStructureCode = $dataQualityChecksService->onVersionGeoStructureCode($importMappingRepository, $currentMappingRepository);
            if (count($DQCOnVersionGeoStructureCode) != 0)
            {
                if (array_key_exists('error_message', $DQCOnVersionGeoStructureCode)) {
                    return $this->render('GSRM/error_version_geo_structure_code.html.twig', array(
                        'error_message' => $DQCOnVersionGeoStructureCode['error_message'],
                        'client_output_id' => $DQCOnVersionGeoStructureCode['client_output_id'],
                        'distinct_version_geo_structure_code' => $DQCOnVersionGeoStructureCode['distinct_version_geo_structure_code']
                    ));
                } else {
                    for ($row = 0; $row < count($DQCOnVersionGeoStructureCode); $row++) {
                        $dataQualityCheck = new GsrmDataQualityChecks();
                        $dataQualityCheck->setClientOutputId($DQCOnVersionGeoStructureCode[$row]['client_output_id']);
                        $dataQualityCheck->setLoadDate($currentLoadDate);
                        $dataQualityCheck->setStatus($DQCOnVersionGeoStructureCode[$row]['status']);
                        $dataQualityCheck->setInfo($DQCOnVersionGeoStructureCode[$row]['info']);
                        $em->persist($dataQualityCheck);

                        //$dataQualityChecks[] = array(
                            //'client_output_id' => $DQCOnVersionGeoStructureCode[$row]['client_output_id'],
                            //'status' => $DQCOnVersionGeoStructureCode[$row]['status'],
                            //'info' => $DQCOnVersionGeoStructureCode[$row]['info']
                        //);

                    }
                }
            }
            */

            // DQC on mappings
            /*
            $DQCOnMappings = $dataQualityChecksService->onMappings($importMappingRepository, $currentMappingRepository, $DwhDimGeoSalesRepRepository, $em);
            for ($row = 0; $row < count($DQCOnMappings); $row++) {
                $dataQualityCheck = new GsrmDataQualityChecks();
                $dataQualityCheck->setClientOutputId($DQCOnMappings[$row]['client_output_id']);
                $dataQualityCheck->setLoadDate($currentLoadDate);
                $dataQualityCheck->setStatus($DQCOnMappings[$row]['status']);
                $dataQualityCheck->setInfo($DQCOnMappings[$row]['info']);
                $em->persist($dataQualityCheck);

                //$dataQualityChecks[] = array(
                    //'client_output_id' => $DQCOnMappings[$row]['client_output_id'],
                    //'status' => $DQCOnMappings[$row]['status'],
                    //'info' => $DQCOnMappings[$row]['info']
                //);

            }*/

            //$em->flush();



            //-------------------------
            /*
            // Loop on client_output_id
            $distinctClientoutputidInImportMapping = $importMappingRepository->getDistinctClientOutputId();
            for ($row = 0; $row < count($distinctClientoutputidInImportMapping); $row++)
            {
                $currentClientoutputid = $distinctClientoutputidInImportMapping[$row]['clientOutputId']; // Current Client_output_id
                // Check if version_geo_structure_code is unique
                $distinctVersionGeoStructureCodeInImportMapping = $importMappingRepository->getDistinctVersionGeoStructureCode($currentClientoutputid);
                $distinctVersionGeoStructureCodeInCurrentMapping = $currentMappingRepository->getDistinctVersionGeoStructureCode($currentClientoutputid);
                if (count($distinctVersionGeoStructureCodeInImportMapping) > '1')
                {
                   return $this->render('GSRM/error_version_geo_structure_code.html.twig', array(
                        'error_message' => 'Version_geo_structure_code must be unique for one ClientoutputId. ',
                        'client_output_id' => $currentClientoutputid,
                        'distinct_version_geo_structure_code' => $distinctVersionGeoStructureCodeInImportMapping
                    ));
                }

                // Check if first mapping for this client_output_id
                if (count($distinctVersionGeoStructureCodeInCurrentMapping) == 0)
                {

                    // ---------------------------------------
                    // 3 - geoLevelNumber
                    // ---------------------------------------
                    //$distinctGeoLevelInImportMapping = $importMappingRepository->getDistinctValuesInColumn('geoLevelNumber', $currentClientoutputid);
                    $distinctGeoLevelInImportMapping = $importMappingRepository->getDistinctGeoLevelNumber($currentClientoutputid);

                    $importGeoLevelArray = array(); // Store value in array to be compared
                    for ($i = 0; $i < count($distinctGeoLevelInImportMapping); $i++)
                    {
                        $importGeoLevelArray[] = $distinctGeoLevelInImportMapping[$i]['geoLevelNumber'];
                    }

                    // ---------------------------------------
                    // 4 - geoValue
                    // ---------------------------------------
                    for ($geoLevelRow = 0; $geoLevelRow < count($importGeoLevelArray); $geoLevelRow++)
                    {
                        $geoLevel = $importGeoLevelArray[$geoLevelRow];
                        $distinctGeoValueInImportMapping = $importMappingRepository->getDistinctGeoName($currentClientoutputid, $geoLevel);
                        $distinctGeoValueInDWH = $DwhDimGeoSalesRepRepository->getDistinctValuesInColumn('geoLevel'.$geoLevel, $currentClientoutputid);

                        $importGeoValueArray = array(); // Store value in array to be compared
                        for ($i = 0; $i < count($distinctGeoValueInImportMapping); $i++) {
                            $importGeoValueArray[] = $distinctGeoValueInImportMapping[$i]['geoValue'];
                        }

                        $DwhGeoValueArray = array(); // Store value in array to be compared
                        for ($u = 0; $u < count($distinctGeoValueInDWH); $u++) {
                            $DwhGeoValueArray[] = $distinctGeoValueInDWH[$u]['geoLevel'.$geoLevel];
                        }

                        $comparisonImportWithDwh = array_diff($importGeoValueArray, $DwhGeoValueArray);
                        $comparisonDwhWithImport = array_diff($DwhGeoValueArray, $importGeoValueArray);

                        // Check if we have unexpected geo in new values regarding the geo_level_number
                        if (count($comparisonImportWithDwh) > '0') {
                            foreach ($comparisonImportWithDwh as $item) {
                                $dataQualityCheck = new GsrmDataQualityChecks();
                                $dataQualityCheck->setClientOutputId($currentClientoutputid);
                                $dataQualityCheck->setLoadDate($currentLoadDate);
                                $dataQualityCheck->setAnalyzedField('geo_value');
                                $dataQualityCheck->setStatus('WARNING');
                                $dataQualityCheck->setInfo('Unexpected geo (NameLevel) "' . $item . '" for level ' . $geoLevel . '.');
                                $em->persist($dataQualityCheck);
                            }
                        }

                        // Check if we have a mapping for all regions regarding the geo_level_number
                        if (count($comparisonDwhWithImport) > '0') {
                            foreach ($comparisonDwhWithImport as $item) {
                                $dataQualityCheck = new GsrmDataQualityChecks();
                                $dataQualityCheck->setClientOutputId($currentClientoutputid);
                                $dataQualityCheck->setLoadDate($currentLoadDate);
                                $dataQualityCheck->setAnalyzedField('geo_value');
                                $dataQualityCheck->setStatus('WARNING');
                                $dataQualityCheck->setInfo('There is currently no mapping for geo (NameLevel) "' . $item . '" on level ' . $geoLevel . '.');
                                $em->persist($dataQualityCheck);
                            }
                        }
                    }
                }
                else {
                    if ($distinctVersionGeoStructureCodeInImportMapping == $distinctVersionGeoStructureCodeInCurrentMapping) {
                        $dataQualityCheck = new GsrmDataQualityChecks();
                        $dataQualityCheck->setClientOutputId($currentClientoutputid);
                        $dataQualityCheck->setLoadDate($currentLoadDate);
                        $dataQualityCheck->setAnalyzedField('version_geo_structure_code');
                        $dataQualityCheck->setStatus('CORRECT');
                        $dataQualityCheck->setInfo('No change.');
                    } else {
                        $dataQualityCheck = new GsrmDataQualityChecks();
                        $dataQualityCheck->setClientOutputId($currentClientoutputid);
                        $dataQualityCheck->setLoadDate($currentLoadDate);
                        $dataQualityCheck->setAnalyzedField('version_geo_structure_code');
                        $dataQualityCheck->setStatus('WARNING');
                        $dataQualityCheck->setInfo('New name = ' . $distinctVersionGeoStructureCodeInImportMapping[0]['versionGeoStructureCode'] . ' (OLD NAME was ' . $distinctVersionGeoStructureCodeInCurrentMapping[0]['versionGeoStructureCode'] . '.');
                    }

                    $em->persist($dataQualityCheck);

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

                    // ---------------------------------------
                    // 3 - geoLevelNumber
                    // ---------------------------------------

                    $distinctGeoLevelInImportMapping = $importMappingRepository->getDistinctValuesInColumn('geoLevelNumber', $currentClientoutputid);
                    $distinctGeoLevelInCurrentMapping = $currentMappingRepository->getDistinctValuesInColumn('geoLevelNumber', $currentClientoutputid);

                    $importGeoLevelArray = array(); // Store value in array to be compared
                    for ($i = 0; $i < count($distinctGeoLevelInImportMapping); $i++) {
                        $importGeoLevelArray[] = $distinctGeoLevelInImportMapping[$i]['geoLevelNumber'];
                    }

                    $currentGeoLevelArray = array(); // Store value in array to be compared
                    for ($u = 0; $u < count($distinctGeoLevelInCurrentMapping); $u++) {
                        $currentGeoLevelArray[] = $distinctGeoLevelInCurrentMapping[$u]['geoLevelNumber'];
                    }

                    $comparisonGeoLevelFromImport = array_diff($importGeoLevelArray, $currentGeoLevelArray);
                    $comparisonGeoLevelFromCurrent = array_diff($currentGeoLevelArray, $importGeoLevelArray);

                    // Check for New and Removed values
                    if (count($comparisonGeoLevelFromImport) > '0') {
                        foreach ($comparisonGeoLevelFromImport as $item) {
                            $dataQualityCheck = new GsrmDataQualityChecks();
                            $dataQualityCheck->setClientOutputId($currentClientoutputid);
                            $dataQualityCheck->setLoadDate($currentLoadDate);
                            $dataQualityCheck->setAnalyzedField('geo_level');
                            $dataQualityCheck->setStatus('WARNING');
                            $dataQualityCheck->setInfo('New level "' . $item . '".');
                            $em->persist($dataQualityCheck);
                        }
                    } elseif (count($comparisonGeoLevelFromCurrent) > '0') {
                        foreach ($comparisonGeoLevelFromCurrent as $item) {
                            $dataQualityCheck = new GsrmDataQualityChecks();
                            $dataQualityCheck->setClientOutputId($currentClientoutputid);
                            $dataQualityCheck->setLoadDate($currentLoadDate);
                            $dataQualityCheck->setAnalyzedField('geo_level');
                            $dataQualityCheck->setStatus('WARNING');
                            $dataQualityCheck->setInfo('Level "' . $item . '" will be removed.');
                            $em->persist($dataQualityCheck);
                        }
                    } else {
                        $dataQualityCheck = new GsrmDataQualityChecks();
                        $dataQualityCheck->setClientOutputId($currentClientoutputid);
                        $dataQualityCheck->setLoadDate($currentLoadDate);
                        $dataQualityCheck->setAnalyzedField('geo_level');
                        $dataQualityCheck->setStatus('CORRECT');
                        $dataQualityCheck->setInfo('No change.');
                    }

                    $em->persist($dataQualityCheck);

                    // ---------------------------------------
                    // 4 - geoValue
                    // ---------------------------------------

                    for ($geoLevelRow = 0; $geoLevelRow < count($importGeoLevelArray); $geoLevelRow++) {

                        $geoLevel = $importGeoLevelArray[$geoLevelRow];

                        $distinctGeoValueInImportMapping = $importMappingRepository->getDistinctGeoName($currentClientoutputid, $geoLevel);
                        $distinctGeoValueInCurrentMapping = $currentMappingRepository->getDistinctGeoName($currentClientoutputid, $geoLevel);
                        $distinctGeoValueInDWH = $DwhDimGeoSalesRepRepository->getDistinctValuesInColumn('geoLevel'.$geoLevel, $currentClientoutputid);

                        $importGeoValueArray = array(); // Store value in array to be compared
                        for ($i = 0; $i < count($distinctGeoValueInImportMapping); $i++) {
                            $importGeoValueArray[] = $distinctGeoValueInImportMapping[$i]['geoValue'];
                        }

                        $currentGeoValueArray = array(); // Store value in array to be compared
                        for ($u = 0; $u < count($distinctGeoValueInCurrentMapping); $u++) {
                            $currentGeoValueArray[] = $distinctGeoValueInCurrentMapping[$u]['geoValue'];
                        }

                        $DwhGeoValueArray = array(); // Store value in array to be compared
                        for ($u = 0; $u < count($distinctGeoValueInDWH); $u++) {
                            $DwhGeoValueArray[] = $distinctGeoValueInDWH[$u]['geoLevel'.$geoLevel];
                        }

                        $comparisonGeoValueFromImport = array_diff($importGeoValueArray, $currentGeoValueArray);
                        $comparisonGeoValueFromCurrent = array_diff($currentGeoValueArray, $importGeoValueArray);
                        $comparisonImportWithDwh = array_diff($importGeoValueArray, $DwhGeoValueArray);

                        // Check for New and Removed values
                        if (count($comparisonGeoValueFromImport) > '0') {
                            foreach ($comparisonGeoValueFromImport as $item) {
                                $dataQualityCheck = new GsrmDataQualityChecks();
                                $dataQualityCheck->setClientOutputId($currentClientoutputid);
                                $dataQualityCheck->setLoadDate($currentLoadDate);
                                $dataQualityCheck->setAnalyzedField('geo_value');
                                $dataQualityCheck->setStatus('WARNING');
                                $dataQualityCheck->setInfo('New geo (NameLevel) "' . $item . '" for level ' . $geoLevel . '.');
                                $em->persist($dataQualityCheck);
                            }
                        } elseif (count($comparisonGeoValueFromCurrent) > '0') {
                            foreach ($comparisonGeoValueFromCurrent as $item) {
                                $dataQualityCheck = new GsrmDataQualityChecks();
                                $dataQualityCheck->setClientOutputId($currentClientoutputid);
                                $dataQualityCheck->setLoadDate($currentLoadDate);
                                $dataQualityCheck->setAnalyzedField('geo_value');
                                $dataQualityCheck->setStatus('WARNING');
                                $dataQualityCheck->setInfo('Geo (NameLevel) "' . $item . '" for level ' . $geoLevel . ' will be removed.');
                                $em->persist($dataQualityCheck);
                            }
                        } else {
                            $dataQualityCheck = new GsrmDataQualityChecks();
                            $dataQualityCheck->setClientOutputId($currentClientoutputid);
                            $dataQualityCheck->setLoadDate($currentLoadDate);
                            $dataQualityCheck->setAnalyzedField('geo_value');
                            $dataQualityCheck->setStatus('CORRECT');
                            $dataQualityCheck->setInfo('No change.');
                            $em->persist($dataQualityCheck);
                        }

                        // Check if we have unexpected geo in new values regarding the geo_level_number
                        if (count($comparisonImportWithDwh) > '0') {
                            foreach ($comparisonImportWithDwh as $item) {
                                $dataQualityCheck = new GsrmDataQualityChecks();
                                $dataQualityCheck->setClientOutputId($currentClientoutputid);
                                $dataQualityCheck->setLoadDate($currentLoadDate);
                                $dataQualityCheck->setAnalyzedField('geo_value');
                                $dataQualityCheck->setStatus('WARNING');
                                $dataQualityCheck->setInfo('Unexpected geo (NameLevel) "' . $item . '" for level ' . $geoLevel . '.');
                                $em->persist($dataQualityCheck);
                            }
                        }

                        // Check if we have a mapping for all regions regarding the geo_level_number
                        $comparisonDwhWithImport= array_diff($DwhGeoValueArray, $importGeoValueArray);
                        if (count($comparisonDwhWithImport) > '0') {
                            foreach ($comparisonDwhWithImport as $item) {
                                $dataQualityCheck = new GsrmDataQualityChecks();
                                $dataQualityCheck->setClientOutputId($currentClientoutputid);
                                $dataQualityCheck->setLoadDate($currentLoadDate);
                                $dataQualityCheck->setAnalyzedField('geo_value');
                                $dataQualityCheck->setStatus('WARNING');
                                $dataQualityCheck->setInfo('There is currently no mapping for NameLevel "' . $item . '" on level ' . $geoLevel . '.');
                                $em->persist($dataQualityCheck);
                            }
                        }
                    }


                    // ---------------------------------------
                    // 5 - SalesRep
                    // ---------------------------------------
                    $distinctSalesRepInImportMapping = $importMappingRepository->getDistinctSalesRep($currentClientoutputid);
                    $distinctSalesRepInCurrentMapping = $currentMappingRepository->getDistinctSalesRep($currentClientoutputid);

                    $importSalesRepArray = array(); // Store value in array to be compared
                    for ($i = 0; $i < count($distinctSalesRepInImportMapping); $i++) {
                        $importSalesRepArray[] = $distinctSalesRepInImportMapping[$i]['srFirstName'] . ' ' . $distinctSalesRepInImportMapping[$i]['srLastName'];
                    }

                    $currentSalesRepArray = array(); // Store value in array to be compared
                    for ($u = 0; $u < count($distinctSalesRepInCurrentMapping); $u++) {
                        $currentSalesRepArray[] = $distinctSalesRepInCurrentMapping[$u]['srFirstName'] . ' ' . $distinctSalesRepInCurrentMapping[$u]['srLastName'];
                    }

                    $comparisonSalesRepFromImport = array_diff($importSalesRepArray, $currentSalesRepArray);
                    $comparisonSalesRepFromCurrent = array_diff($currentSalesRepArray, $importSalesRepArray);

                    $totalSize = 0;
                    $totalSize = count($comparisonSalesRepFromImport)+count($comparisonSalesRepFromCurrent);
                    if (count($comparisonSalesRepFromImport) > '0') {
                        foreach ($comparisonSalesRepFromImport as $item) {
                            $dataQualityCheck = new GsrmDataQualityChecks();
                            $dataQualityCheck->setClientOutputId($currentClientoutputid);
                            $dataQualityCheck->setLoadDate($currentLoadDate);
                            $dataQualityCheck->setAnalyzedField('sales_rep');
                            $dataQualityCheck->setStatus('WARNING');
                            $dataQualityCheck->setInfo('New SalesRep "' . $item . '".');
                            $em->persist($dataQualityCheck);
                        }
                    } elseif (count($comparisonSalesRepFromCurrent) > '0') {
                        foreach ($comparisonSalesRepFromCurrent as $item) {
                            $dataQualityCheck = new GsrmDataQualityChecks();
                            $dataQualityCheck->setClientOutputId($currentClientoutputid);
                            $dataQualityCheck->setLoadDate($currentLoadDate);
                            $dataQualityCheck->setAnalyzedField('sales_rep');
                            $dataQualityCheck->setStatus('WARNING');
                            $dataQualityCheck->setInfo('SalesRep "' . $item . '" will be removed.');
                            $em->persist($dataQualityCheck);
                        }
                    } else {
                        $dataQualityCheck = new GsrmDataQualityChecks();
                        $dataQualityCheck->setClientOutputId($currentClientoutputid);
                        $dataQualityCheck->setLoadDate($currentLoadDate);
                        $dataQualityCheck->setAnalyzedField('sales_rep');
                        $dataQualityCheck->setStatus('CORRECT');
                        $dataQualityCheck->setInfo('No change.');
                    }

                    $em->persist($dataQualityCheck);
                }
            }
            $em->flush();
            */

            // Import Form return
            return $this->redirectToRoute('gsrm_viewLoadResult', array(
              'currentLoadDate' => $currentLoadDate
            ));

        }

        // ================================================================================================================
        // Export Template
        // ================================================================================================================

        $geoSalesRepExportTemplateFile = new GsrmExportMappingTemplateFile();
        $geoSalesRepExportTemplateForm = $this->createForm(GsrmExportMappingTemplateFileType::class, $geoSalesRepExportTemplateFile);

        if ($request->isMethod('POST') && $geoSalesRepExportTemplateForm->handleRequest($request)->isValid())
        {
            $datasetID = $geoSalesRepExportTemplateFile->getDatasetName();
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('AppBundle:GsrmCurrentMapping');
            $exportMapping = $this->get('GsrmExportMapping');
            $response = $exportMapping->exportMapping($datasetID, $repository);
            return $response;
        }

        // ================================================================================================================
        // View current mapping
        // ================================================================================================================

        $geoSalesRepViewMapping = new GsrmViewCurrentMapping();
        $geoSalesRepViewMappingForm = $this->createForm(GsrmViewCurrentMappingType::class, $geoSalesRepViewMapping);

        if ($request->isMethod('POST') && $geoSalesRepViewMappingForm->handleRequest($request)->isValid()) {

            $datasetID = $geoSalesRepViewMapping->getDataset();

            return $this->redirectToRoute('gsrm_mapping', array(
              'clientoutputid' => $datasetID
            ));

        }

        // ================================================================================================================
        // General return
        // ================================================================================================================

        return $this->render('GSRM/index.html.twig', array(
            'importGeoSalesRepForm' => $importGeoSalesRepForm->createView(),
            'GeoSalesRepExportTemplateForm' => $geoSalesRepExportTemplateForm->createView(),
            'GeoSalesRepViewMappingForm' => $geoSalesRepViewMappingForm->createView(),
        ));
    }


    /**
     * @Route("/gsrm/load_result", name="gsrm_viewLoadResult")
     */
    public function viewLoadResultAction(Request $request)
    {
        $loadDate = $request->query->get('currentLoadDate');

        $em = $this->getDoctrine()->getManager();
        $dataQualityChecksRepository = $em->getRepository('AppBundle:GsrmDataQualityChecks');
        $dataQualityChecks = $dataQualityChecksRepository->findBy(array('loadDate' => $loadDate),array('id' => 'asc'), null, null);

        $importMappingRepository = $em->getRepository('AppBundle:GsrmImportMapping');
        $importGeoSalesRepMappings = $importMappingRepository->findAll();

        $currentMappingRepository = $em->getRepository('AppBundle:GsrmCurrentMapping');

        // Number of distinct clientoutputID
        $dataQualityChecksDistinctClientoutputId = $dataQualityChecksRepository->getDistinctClientOutputId($loadDate);
        $nbrOfDistinctClientoutputId = count($dataQualityChecksDistinctClientoutputId);

        // Number of WARNINGS by ClientoutputId
        $nbrOfWarningsByClientoutputId = array();
        for ($row = 0; $row < $nbrOfDistinctClientoutputId; $row++) {
            $clientoutputId = $dataQualityChecksDistinctClientoutputId[$row]['clientOutputId'];
            $nbrOfWarningsByClientoutputId[] = array(
                'clientoutputId' => $clientoutputId,
                'nbr_of_warnings' => count($dataQualityChecksRepository->getWarningsForOneClientoutputId($loadDate, $clientoutputId)),
                'nbr_of_current_mappings' => $currentMappingRepository->getNumberOfMappings($clientoutputId)
            );
        }

        // Total number of WARNINGS in Data quality checks
        $totalNbrOfWarnings = 0;
        for ($row = 0; $row < count($nbrOfWarningsByClientoutputId); $row++) {
            $totalNbrOfWarnings += $nbrOfWarningsByClientoutputId[$row]['nbr_of_warnings'];
        }

        // Confirm Form
        $confirmImportMappingForm = $this->createFormBuilder()
            ->add('cancel', SubmitType::class, array('label' => 'Cancel'))
            ->add('confirm', SubmitType::class, array('label' => 'Confirm changes'))
            ->getForm();

        $confirmImportMappingForm->handleRequest($request);

        if ($confirmImportMappingForm->isSubmitted() && $confirmImportMappingForm->isValid()) {
            if($confirmImportMappingForm->get('cancel')->isClicked())
            {
                // Remove Data Quality Checks for $loadDate
                while ($dataQualityChecksRepository->findOneBy(array('loadDate' => $loadDate))) {
                    $itemsToRemove = $dataQualityChecksRepository->findOneBy(array('loadDate' => $loadDate));
                    $em->remove($itemsToRemove);
                    $em->flush();
                }
                // Truncate table "gsrm_import_mapping"
                $connection = $em->getConnection();
                $platform = $connection->getDatabasePlatform();
                $connection->executeUpdate($platform->getTruncateTableSQL('gsrm_import_mapping', true));
                // Return
                return $this->redirectToRoute('gsrm_index');
            }
            if($confirmImportMappingForm->get('confirm')->isClicked())
            {
                // Delete current mappings before adding the new ones
                $em = $this->getDoctrine()->getManager();
                $currentMappingRepository = $em->getRepository('AppBundle:GsrmCurrentMapping');
                $importMappingRepository = $em->getRepository('AppBundle:GsrmImportMapping');

                $distinctClientoutputidInImportMapping = $importMappingRepository->getDistinctClientOutputId();
                $nbrDistinctImportClientoutputid = count($distinctClientoutputidInImportMapping);

                for ($row = 0; $row < $nbrDistinctImportClientoutputid; $row++) {

                    $currentImportClientoutputid = $distinctClientoutputidInImportMapping[$row]['clientOutputId']; // Current Client_output_id

                    while ($currentMappingRepository->findOneBy(array('clientOutputId' => $currentImportClientoutputid))) {
                        $itemsToRemove = $currentMappingRepository->findOneBy(array('clientOutputId' => $currentImportClientoutputid));
                        $em->remove($itemsToRemove);
                        $em->flush();
                    }
                }

                // Adding new mappings
                $em = $this->getDoctrine()->getManager();
                $currentMappingRepository = $em->getRepository('AppBundle:GsrmCurrentMapping');
                $importMappingRepository = $em->getRepository('AppBundle:GsrmImportMapping');
                $importGeoSalesRepMappings = $importMappingRepository->findAll();
                foreach ($importGeoSalesRepMappings as $mapping) {
                    $geoSalesRep = new GsrmCurrentMapping();
                    $geoSalesRep->setClientOutputId($mapping->getClientOutputId());
                    $geoSalesRep->setVersionGeoStructureCode($mapping->getVersionGeoStructureCode());
                    $geoSalesRep->setGeoTeam($mapping->getGeoTeam());
                    $geoSalesRep->setGeoLevelNumber($mapping->getGeoLevelNumber());
                    $geoSalesRep->setGeoValue($mapping->getGeoValue());
                    $geoSalesRep->setSrFirstName($mapping->getSrFirstName());
                    $geoSalesRep->setSrLastName($mapping->getSrLastName());
                    $geoSalesRep->setSrEmail($mapping->getSrEmail());
                    $em->persist($geoSalesRep);
                }
                $em->flush();

                // Return
                return $this->render('GSRM/loaded.html.twig');
                //return $this->redirectToRoute('gsrm_loaded');
            }
        }


        return $this->render('GSRM/load_result.html.twig', array(
            'dataQualityChecks' => $dataQualityChecks,
            'importMapping' => $importGeoSalesRepMappings,
            'nbrOfWarningsByClientoutputId' => $nbrOfWarningsByClientoutputId,
            'totalNbrOfWarnings' => $totalNbrOfWarnings,
            'confirmForm' => $confirmImportMappingForm->createView(),
        ));
    }





    /**
     * @Route("/gsrm/mapping/{clientoutputid}", name="gsrm_mapping")
     */
    public function viewCurrentMappingAction($clientoutputid, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:GsrmCurrentMapping');
        $geoSalesRepMappings = $repository->findBy(array('clientOutputId' => $clientoutputid));

        // ================================================================================================================
        // View current mapping
        // ================================================================================================================

        $geoSalesRepViewMapping = new GsrmViewCurrentMapping();
        $geoSalesRepViewMappingForm = $this->createForm(GsrmViewCurrentMappingType::class, $geoSalesRepViewMapping);

        if ($request->isMethod('POST') && $geoSalesRepViewMappingForm->handleRequest($request)->isValid())
        {
            $datasetID = $geoSalesRepViewMapping->getDataset();
            return $this->redirectToRoute('gsrm_mapping', array(
              'clientoutputid' => $datasetID
            ));
        }

        // ================================================================================================================
        // Download this mapping
        // ================================================================================================================

        $exportMappingFile = new GsrmExportMappingTemplateFile();
        $exportMappingFile->setDatasetName($clientoutputid);

        $downloadForm = $this->createFormBuilder($exportMappingFile)
            ->add('download', SubmitType::class, array('label' => 'Download this mapping'))
            ->getForm()
        ;

        if ($request->isMethod('POST') && $downloadForm->handleRequest($request)->isValid())
        {
            $exportMapping = $this->get('GsrmExportMapping');
            $response = $exportMapping->exportMapping($clientoutputid, $repository);
            return $response;
        }

        // ================================================================================================================
        // Return
        // ================================================================================================================

        return $this->render('GSRM/view_mapping.html.twig', array(
            'results' => $geoSalesRepMappings,
            'GeoSalesRepViewMappingForm' => $geoSalesRepViewMappingForm->createView(),
            'datasetId' => $clientoutputid,
            'downloadForm' => $downloadForm->createView(),
        ));
    }


    /**
     * @Route("/gsrm/monitoring/{id}")
     */
    public function viewMonitoringAction($id)
    {
        return new Response("On affichera ici un écran avec le monitoring choisi par l'id : ".$id);
    }


    /**
     * @Route("/gsrm/test", name="gsrm_test")
     */
    public function testAction()
    {
        return new Response("On affichera ici un écran avec la vue du mapping actuel pour le ClientOutputID : ");
    }

    /**
     * @Route("/gsrm/verif", name="gsrm_verif")
     */
    public function verifAction()
    {
      return new Response("On affichera ici un écran avec la vue du mapping actuel pour le ClientOutputID : ");
    }

}
