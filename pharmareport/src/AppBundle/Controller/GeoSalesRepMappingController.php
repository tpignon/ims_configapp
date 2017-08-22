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

            $importMappingRepository = $em->getRepository('AppBundle:GsrmImportMapping');
            $currentMappingRepository = $em->getRepository('AppBundle:GsrmCurrentMapping');
            $DwhDimGeoSalesRepRepository = $em->getRepository('AppBundle:DwhDimGeoSalesRep');
            $dataQualityChecksService = $this->get('GsrmDataQualityChecks');

            // DQC on version_geo_structure_code
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
                    }
                }
            }

            // DQC on mappings
            $DQCOnMappings = $dataQualityChecksService->onMappings($importMappingRepository, $currentMappingRepository, $DwhDimGeoSalesRepRepository, $em);
            for ($row = 0; $row < count($DQCOnMappings); $row++) {
                $dataQualityCheck = new GsrmDataQualityChecks();
                $dataQualityCheck->setClientOutputId($DQCOnMappings[$row]['client_output_id']);
                $dataQualityCheck->setLoadDate($currentLoadDate);
                $dataQualityCheck->setStatus($DQCOnMappings[$row]['status']);
                $dataQualityCheck->setInfo($DQCOnMappings[$row]['info']);
                $em->persist($dataQualityCheck);
            }

            $em->flush();

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
        $importMappingRepository = $em->getRepository('AppBundle:GsrmImportMapping');
        $currentMappingRepository = $em->getRepository('AppBundle:GsrmCurrentMapping');
        $dataQualityChecksRepository = $em->getRepository('AppBundle:GsrmDataQualityChecks');
        $dataQualityChecks = $dataQualityChecksRepository->findBy(array('loadDate' => $loadDate),array('id' => 'asc'), null, null);
        $importGeoSalesRepMappings = $importMappingRepository->findAll();

        // Number of distinct clientoutputID
        $dataQualityChecksDistinctClientoutputId = $dataQualityChecksRepository->getDistinctClientOutputId($loadDate);

        // Status by ClientoutputId
        $statusByClientoutputId = array();
        for ($row = 0; $row < count($dataQualityChecksDistinctClientoutputId); $row++) {
            $clientoutputId = $dataQualityChecksDistinctClientoutputId[$row]['clientOutputId'];
            $statusByClientoutputId[] = array(
                'clientoutputId' => $clientoutputId,
                'nbr_of_warnings' => $dataQualityChecksRepository->getNbrOfWarnings($loadDate, $clientoutputId),
                'nbr_of_removed_mappings' => $dataQualityChecksRepository->getNbrOfRemovedMappings($loadDate, $clientoutputId),
                'nbr_of_changed_mappings' => $importMappingRepository->getNbrOfChangedMappings($clientoutputId),
                'nbr_of_new_mappings' => $importMappingRepository->getNbrOfNewMappings($clientoutputId),
                'nbr_of_unchanged_mappings' => $importMappingRepository->getNbrOfUnchangedMappings($clientoutputId),
                'nbr_of_current_mappings' => $currentMappingRepository->getNbrOfMappings($clientoutputId),
                'nbr_of_import_mappings' => $importMappingRepository->getNbrOfMappings($clientoutputId)
            );
        }

        // Overview status
        $totalNbrOfWarnings = 0;
        $totalNbrOfRemovedMappings = 0;
        $totalNbrOfChangedMappings = 0;
        $totalNbrOfNewMappings = 0;
        $totalNbrOfUnchangedMappings = 0;
        for ($row = 0; $row < count($statusByClientoutputId); $row++) {
            $totalNbrOfWarnings += $statusByClientoutputId[$row]['nbr_of_warnings'];
            $totalNbrOfRemovedMappings += $statusByClientoutputId[$row]['nbr_of_removed_mappings'];
            $totalNbrOfChangedMappings += $statusByClientoutputId[$row]['nbr_of_changed_mappings'];
            $totalNbrOfNewMappings += $statusByClientoutputId[$row]['nbr_of_new_mappings'];
            $totalNbrOfUnchangedMappings += $statusByClientoutputId[$row]['nbr_of_unchanged_mappings'];
        }
        $overviewStatus = array(
            'total_nbr_of_warnings' => $totalNbrOfWarnings,
            'total_nbr_of_removed_mappings' => $totalNbrOfRemovedMappings,
            'total_nbr_of_changed_mappings' => $totalNbrOfChangedMappings,
            'total_nbr_of_new_mappings' => $totalNbrOfNewMappings,
            'total_nbr_of_unchanged_mappings' => $totalNbrOfUnchangedMappings
        );

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
            elseif ($confirmImportMappingForm->get('confirm')->isClicked())
            {
                // Delete current mappings before adding the new ones
                $em = $this->getDoctrine()->getManager();
                $currentMappingRepository = $em->getRepository('AppBundle:GsrmCurrentMapping');
                $importMappingRepository = $em->getRepository('AppBundle:GsrmImportMapping');

                $distinctClientoutputidInImportMapping = $importMappingRepository->getDistinctClientOutputId();
                $nbrDistinctImportClientoutputid = count($distinctClientoutputidInImportMapping);

                for ($row = 0; $row < $nbrDistinctImportClientoutputid; $row++)
                {
                    $currentImportClientoutputid = $distinctClientoutputidInImportMapping[$row]['clientOutputId']; // Current Client_output_id
                    while ($currentMappingRepository->findOneBy(array('clientOutputId' => $currentImportClientoutputid)))
                    {
                        $itemsToRemove = $currentMappingRepository->findOneBy(array('clientOutputId' => $currentImportClientoutputid));
                        $em->remove($itemsToRemove);
                        $em->flush();
                    }
                }

                // Adding new mappings
                $em = $this->getDoctrine()->getManager();
                $currentMappingRepository = $em->getRepository('AppBundle:GsrmCurrentMapping');
                $importMappingRepository = $em->getRepository('AppBundle:GsrmImportMapping');
                //$importGeoSalesRepMappings = $importMappingRepository->findAll();
                foreach ($importGeoSalesRepMappings as $mapping)
                {
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
            'statusByClientoutputId' => $statusByClientoutputId,
            'overviewStatus' => $overviewStatus,
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
