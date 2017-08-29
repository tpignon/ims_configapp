<?php
// All files concerning this module "Targets" will have the prefix "Tar" for Targets
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
use AppBundle\Entity\TarCurrentTargets;
use AppBundle\Entity\TarImportTargets;
use AppBundle\Entity\TarImportTargetsFile;
use AppBundle\Entity\TarExportTargetsTemplateFile;
use AppBundle\Entity\TarViewCurrentTargets;
//use AppBundle\Entity\TarDataQualityChecks;
use AppBundle\Form\TarImportTargetsFileType;
use AppBundle\Form\TarExportTargetsTemplateFileType;
use AppBundle\Form\TarViewCurrentTargetsType;

class TargetsController extends Controller
{

    /**
     * @Route("/targets", name="targets_index")
     */
    public function indexAction(Request $request)
    {
        // ================================================================================================================
        // Import file
        // ================================================================================================================

        $importTargetsFile = new TarImportTargetsFile();
        $importTargetsForm = $this->createForm(TarImportTargetsFileType::class, $importTargetsFile);

        if ($request->isMethod('POST') && $importTargetsForm->handleRequest($request)->isValid())
        {
            // --------------------------------------------------------------
            // Import mappings into an array
            // --------------------------------------------------------------
            $currentLoadDate = date('Ymd_His');

            $targetsFileFolder = $this->getParameter('targets_csvfile_folder');
            $targetsFileName = $this->getParameter('targets_csvfile_filename');
            $targetsFile = $targetsFileFolder . '/' . $targetsFileName;
            $file = $importTargetsFile->getImportTargetsFile();
            $file->move($targetsFileFolder, $targetsFileName);

            $tarImportService = $this->get('TarImportTargets');
            $importTargetsArray = array(); // This array will contain elements extracted from csv file
            $importTargetsArray = $tarImportService->importCSV($targetsFile); // Row 0 contains headers
            if (array_key_exists('error_type', $importTargetsArray))
            {
                // File doesn't exist
                if ($importTargetsArray['error_type'] == 'file_does_not_exist')
                {
                    return $this->render('Targets/error_submit_file.html.twig', array(
                        'error_message' => 'File "' . $importTargetsArray['error_file'] . '" doesn\'t exist.',
                    ));
                }
                // Bad number of columns
                if ($importTargetsArray['error_type'] == 'nbr_items_on_row')
                {
                    return $this->render('Targets/error_submit_file.html.twig', array(
                        'error_row' => $importTargetsArray['error_row'],
                        'error_message' => $importTargetsArray['error_nbr_of_columns'] . ' items found --> ' . $importTargetsArray['max_nbr_of_columns'] . ' items (columns) are expected.',
                    ));
                }
            }

            $em = $this->getDoctrine()->getManager();

            // --------------------------------------------------------------
            // Insert targets into MySQL DB, table "tar_import_targets"
            // --------------------------------------------------------------
            for ($row = 1; $row < count($importTargetsArray); $row++) // Start at 1 because the first row contains headers
            {
                $importTargetsEntity = new TarImportTargets();
                $importTargetsEntity->setClientOutputId($importTargetsArray[$row]['client_output_id']);
                $importTargetsEntity->setProductMarketLevel($importTargetsArray[$row]['product_market_level']);
                $importTargetsEntity->setRegionLevel($importTargetsArray[$row]['region_level']);
                $importTargetsEntity->setPeriod($importTargetsArray[$row]['period']);
                $importTargetsEntity->setTargetUnits($importTargetsArray[$row]['target_units']);
                $importTargetsEntity->setMsUnitsTarget($importTargetsArray[$row]['ms_units_target']);
                $importTargetsEntity->setMsValueTarget($importTargetsArray[$row]['ms_value_target']);
                $importTargetsEntity->setTargetValue($importTargetsArray[$row]['target_value']);

                $validator = $this->get('validator');
                $errors = $validator->validate($importTargetsEntity);
                $error_row = $row+1;

                if(count($errors) > 0)
                {
                    $error_message = array();
                    foreach ($errors as $error)
                    {
                        $error_message[] = $error->getMessage();
                    }
                    return $this->render('Targets/error_asserts.html.twig', array(
                        'error_row' => $error_row,
                        'error_message' => $error_message,
                    ));
                }
                else
                {
                    $em->persist($importTargetsEntity);
                }

            }

            // Truncate table "tar_import_targets"
            $connection = $em->getConnection();
            $platform = $connection->getDatabasePlatform();
            $connection->executeUpdate($platform->getTruncateTableSQL('tar_import_targets', true));
            $em->flush();

            // --------------------------------------------------------------
            // Data quality checks (DQC)
            // --------------------------------------------------------------


            // --------------------------------------------------------------
            // Import Form Return
            // --------------------------------------------------------------
            return $this->redirectToRoute('targets_view_load_result', array(
              'currentLoadDate' => $currentLoadDate
            ));

        }

        // ================================================================================================================
        // Export Template
        // ================================================================================================================
        $exportTargetsTemplateFileEntity = new TarExportTargetsTemplateFile();
        $targetsExportTemplateForm = $this->createForm(TarExportTargetsTemplateFileType::class, $exportTargetsTemplateFileEntity);

        if ($request->isMethod('POST') && $targetsExportTemplateForm->handleRequest($request)->isValid())
        {
            $datasetID = $exportTargetsTemplateFileEntity->getDataset();
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('AppBundle:TarCurrentTargets');
            $exportTargetsService = $this->get('TarExportTargets');
            $response = $exportTargetsService->exportTargets($datasetID, $repository);
            return $response;
        }

        // ================================================================================================================
        // View current mapping
        // ================================================================================================================
        $tarViewCurrentTargetsEntity = new TarViewCurrentTargets();
        $tarViewCurrentTargetsForm = $this->createForm(TarViewCurrentTargetsType::class, $tarViewCurrentTargetsEntity);

        if ($request->isMethod('POST') && $tarViewCurrentTargetsForm->handleRequest($request)->isValid())
        {
            $datasetID = $tarViewCurrentTargetsEntity->getDataset();
            return $this->redirectToRoute('targets_view', array(
              'clientoutputid' => $datasetID
            ));
        }

        // ================================================================================================================
        // General return
        // ================================================================================================================
        return $this->render('Targets/index.html.twig', array(
            'importForm' => $importTargetsForm->createView(),
            'exportForm' => $targetsExportTemplateForm->createView(),
            'viewCurrentTargetsForm' => $tarViewCurrentTargetsForm->createView(),
        ));
    }


    /**
     * @Route("/targets/load_result", name="targets_view_load_result")
     */
    public function viewLoadResultAction(Request $request)
    {
        $loadDate = $request->query->get('currentLoadDate');
        $em = $this->getDoctrine()->getManager();
        $importTargetsRepository = $em->getRepository('AppBundle:TarImportTargets');
        $currentTargetsRepository = $em->getRepository('AppBundle:TarCurrentTargets');
        //$dataQualityChecksRepository = $em->getRepository('AppBundle:GsrmDataQualityChecks');
        //$dataQualityChecks = $dataQualityChecksRepository->findBy(array('loadDate' => $loadDate),array('id' => 'asc'), null, null);
        $importTargets = $importTargetsRepository->findAll();

        /*
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
                'nbr_of_changed_mappings' => $importTargetsRepository->getNbrOfChangedMappings($clientoutputId),
                'nbr_of_new_mappings' => $importTargetsRepository->getNbrOfNewMappings($clientoutputId),
                'nbr_of_unchanged_mappings' => $importTargetsRepository->getNbrOfUnchangedMappings($clientoutputId),
                'nbr_of_current_mappings' => $currentTargetsRepository->getNbrOfMappings($clientoutputId),
                'nbr_of_import_mappings' => $importTargetsRepository->getNbrOfMappings($clientoutputId)
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
        */

        // Confirm Form
        $confirmImportTargetsForm = $this->createFormBuilder()
            ->add('cancel', SubmitType::class, array('label' => 'Cancel'))
            ->add('confirm', SubmitType::class, array('label' => 'Confirm changes'))
            ->getForm();

        $confirmImportTargetsForm->handleRequest($request);

        if ($confirmImportTargetsForm->isSubmitted() && $confirmImportTargetsForm->isValid()) {
            if($confirmImportTargetsForm->get('cancel')->isClicked())
            {
                // Remove Data Quality Checks for $loadDate
                /*
                while ($dataQualityChecksRepository->findOneBy(array('loadDate' => $loadDate))) {
                    $itemsToRemove = $dataQualityChecksRepository->findOneBy(array('loadDate' => $loadDate));
                    $em->remove($itemsToRemove);
                    $em->flush();
                }
                */
                // Truncate table "tar_import_targets"
                $connection = $em->getConnection();
                $platform = $connection->getDatabasePlatform();
                $connection->executeUpdate($platform->getTruncateTableSQL('tar_import_targets', true));
                // Return
                return $this->redirectToRoute('targets_index');
            }
            elseif ($confirmImportTargetsForm->get('confirm')->isClicked())
            {
                // Delete current targets before adding the new ones
                $em = $this->getDoctrine()->getManager();
                $currentTargetsRepository = $em->getRepository('AppBundle:TarCurrentTargets');
                $importTargetsRepository = $em->getRepository('AppBundle:TarImportTargets');

                $distinctClientoutputidInImportTargets = $importTargetsRepository->getDistinctClientOutputId();

                for ($row = 0; $row < count($distinctClientoutputidInImportTargets); $row++)
                {
                    $currentImportClientoutputid = $distinctClientoutputidInImportTargets[$row]['clientOutputId']; // Current Client_output_id
                    while ($currentTargetsRepository->findOneBy(array('clientOutputId' => $currentImportClientoutputid)))
                    {
                        $itemsToRemove = $currentTargetsRepository->findOneBy(array('clientOutputId' => $currentImportClientoutputid));
                        $em->remove($itemsToRemove);
                        $em->flush();
                    }
                }

                // Adding new mappings
                $em = $this->getDoctrine()->getManager();
                $currentTargetsRepository = $em->getRepository('AppBundle:TarCurrentTargets');
                $importTargetsRepository = $em->getRepository('AppBundle:TarImportTargets');
                //$importTargets = $importTargetsRepository->findAll();
                foreach ($importTargets as $target)
                {
                    $targetEntity = new TarCurrentTargets();
                    $targetEntity->setClientOutputId($target->getClientOutputId());
                    $targetEntity->setProductMarketLevel($target->getProductMarketLevel());
                    $targetEntity->setRegionLevel($target->getRegionLevel());
                    $targetEntity->setPeriod($target->getPeriod());
                    $targetEntity->setTargetUnits($target->getTargetUnits());
                    $targetEntity->setMsUnitsTarget($target->getMsUnitsTarget());
                    $targetEntity->setMsValueTarget($target->getMsValueTarget());
                    $targetEntity->setTargetValue($target->getTargetValue());
                    $em->persist($targetEntity);
                }
                $em->flush();

                // Truncate table "tar_import_targets"
                $connection = $em->getConnection();
                $platform = $connection->getDatabasePlatform();
                $connection->executeUpdate($platform->getTruncateTableSQL('tar_import_targets', true));

                // Return
                return $this->render('Targets/loaded.html.twig');
                //return $this->redirectToRoute('targets_loaded');
            }
        }


        return $this->render('Targets/load_result.html.twig', array(
            //'dataQualityChecks' => $dataQualityChecks,
            'importTargets' => $importTargets,
            //'statusByClientoutputId' => $statusByClientoutputId,
            //'overviewStatus' => $overviewStatus,
            'confirmForm' => $confirmImportTargetsForm->createView(),
        ));
    }


    /**
     * @Route("/targets/{clientoutputid}", name="targets_view")
     */
    public function viewCurrentTargetsAction($clientoutputid, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:TarCurrentTargets');
        $targets = $repository->findBy(array('clientOutputId' => $clientoutputid));

        // ================================================================================================================
        // View current targets
        // ================================================================================================================
        $viewCurrentTargetsEntity = new TarViewCurrentTargets();
        $viewCurrentTargetsForm = $this->createForm(TarViewCurrentTargetsType::class, $viewCurrentTargetsEntity);

        if ($request->isMethod('POST') && $viewCurrentTargetsForm->handleRequest($request)->isValid())
        {
            $datasetID = $viewCurrentTargetsEntity->getDataset();
            return $this->redirectToRoute('targets_view', array(
              'clientoutputid' => $datasetID
            ));
        }

        // ================================================================================================================
        // Download targets
        // ================================================================================================================
        $exportTargetsTemplateFileEntity = new TarExportTargetsTemplateFile();
        $exportTargetsTemplateFileEntity->setDataset($clientoutputid);

        $downloadForm = $this->createFormBuilder($exportTargetsTemplateFileEntity)
            ->add('download', SubmitType::class)
            ->getForm()
        ;

        if ($request->isMethod('POST') && $downloadForm->handleRequest($request)->isValid())
        {
            $exportTargetsService = $this->get('TarExportTargets');
            $response = $exportTargetsService->exportTargets($clientoutputid, $repository);
            return $response;
        }

        // ================================================================================================================
        // Return
        // ================================================================================================================

        return $this->render('Targets/view_current_targets.html.twig', array(
            'targets' => $targets,
            'viewTargetsForm' => $viewCurrentTargetsForm->createView(),
            'datasetId' => $clientoutputid,
            'downloadForm' => $downloadForm->createView(),
        ));
    }


    /**
     * @Route("/targets/test", name="targets_test")
     */
    public function testAction()
    {
        return new Response("On affichera ici un écran avec la vue du mapping actuel pour le ClientOutputID : ");
    }

    /**
     * @Route("/targets/verif", name="targets_verif")
     */
    public function verifAction()
    {
      return new Response("On affichera ici un écran avec la vue du mapping actuel pour le ClientOutputID : ");
    }

}
