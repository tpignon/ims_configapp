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
use AppBundle\Entity\TarDataQualityChecks;
use AppBundle\Entity\TarExportTargetsTemplateFile;
use AppBundle\Entity\TarImportTargets;
use AppBundle\Entity\TarImportTargetsFile;
use AppBundle\Entity\TarViewCurrentTargets;
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
        ini_set('display_errors', 1);
        $importFile = new TarImportTargetsFile();
        $importFileForm = $this->createForm(TarImportTargetsFileType::class, $importFile);

        if ($request->isMethod('POST') && $importFileForm->handleRequest($request)->isValid())
        {
            // --------------------------------------------------------------
            // Import mappings into an array
            // --------------------------------------------------------------
            $currentLoadDate = date('Ymd_His');

            $targetsFileFolder = $this->getParameter('targets_csvfile_folder');
            $targetsFileName = $this->getParameter('targets_csvfile_filename');
            $targetsFile = $targetsFileFolder . '/' . $targetsFileName;
            $file = $importFile->getImportTargetsFile();
            $file->move($targetsFileFolder, $targetsFileName);

            $importTargetsArray = array(); // This array will contain elements extracted from csv file
            $importTargetsArray = $this->get('ImportFile')->importCSV($targetsFile, 8); // Row 0 contains headers
            if (array_key_exists('error', $importTargetsArray))
            {
                return $this->render('Alerts/error_submit_file.html.twig', array(
                    'error' => $importTargetsArray['error'],
                ));
            }

            // --------------------------------------------------------------
            // Insert targets into MySQL DB, table "tar_import_targets"
            // --------------------------------------------------------------
            $em = $this->getDoctrine()->getManager();
            $formatNumberService = $this->get('app.format_value');
            for ($row = 1; $row < count($importTargetsArray); $row++) {// Start at 1 because the first row contains headers
                $importTargetsEntity = new TarImportTargets();
                $importTargetsEntity->setClientOutputId($importTargetsArray[$row][0]);
                $importTargetsEntity->setProductMarketLevel($importTargetsArray[$row][1]);
                $importTargetsEntity->setRegionLevel($importTargetsArray[$row][2]);
                $importTargetsEntity->setPeriod($importTargetsArray[$row][3]);
                $importTargetsEntity->setTargetUnits($formatNumberService->tofloat(str_replace(' ', '', $importTargetsArray[$row][4])));
                $importTargetsEntity->setMsUnitsTarget($formatNumberService->tofloat(str_replace(' ', '', $importTargetsArray[$row][5])));
                $importTargetsEntity->setMsValueTarget($formatNumberService->tofloat(str_replace(' ', '', $importTargetsArray[$row][6])));
                $importTargetsEntity->setTargetValue($formatNumberService->tofloat(str_replace(' ', '', $importTargetsArray[$row][7])));

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
                    return $this->render('Alerts/error_asserts.html.twig', array(
                        'error_row' => $error_row,
                        'error_message' => $error_message,
                    ));
                }
                else {
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
            $importRepository = $em->getRepository('AppBundle:TarImportTargets');
            $currentRepository = $em->getRepository('AppBundle:TarCurrentTargets');
            $dwhRepository = $em->getRepository('AppBundle:DwhCustomHierarchiesForTargets');
            $dqcRepository = $em->getRepository('AppBundle:TarDataQualityChecks');

            $duplicateImportTargetsEntitiesArray = array();
            $dataQualityChecksArray = array();

            // On parcourt chaque import target
            $importTargetsEntities = $importRepository->findAll();
            foreach ($importTargetsEntities as $importTargetsEntity) {
                // On définit son status grace aux méthodes suivantes:

                // - isUnexpected
                if ($this->get('app.unexpected_data')->isUnexpected($dwhRepository, array(
                    'clientOutputId' => $importTargetsEntity->getClientOutputId(),
                    'productMarketLevel' => $importTargetsEntity->getProductMarketLevel(),
                    'regionLevel' => $importTargetsEntity->getRegionLevel()
                ))) {
                    $importTargetsEntity->setTargetStatus('UNEXPECTED');
                    // Data quality check
                    $dataQualityChecksArray[] = array(
                        'clientOutputId' => $importTargetsEntity->getClientOutputId(),
                        'status' => 'WARNING',
                        'info' => 'Unexpected target: product market "' . $importTargetsEntity->getProductMarketLevel() . '", region "' . $importTargetsEntity->getRegionLevel() . '".'
                    );
                }

                // - isNew
                if ($this->get('app.new_data')->isNew($currentRepository, array(
                    'clientOutputId' => $importTargetsEntity->getClientOutputId(),
                    'productMarketLevel' => $importTargetsEntity->getProductMarketLevel(),
                    'regionLevel' => $importTargetsEntity->getRegionLevel(),
                    'period' => $importTargetsEntity->getPeriod()
                ))) {
                    $importTargetsEntity->setTargetStatus('NEW');
                    // Data quality check
                    $dataQualityChecksArray[] = array(
                        'clientOutputId' => $importTargetsEntity->getClientOutputId(),
                        'status' => 'NEW TARGET',
                        'info' => 'New target: product market "' . $importTargetsEntity->getProductMarketLevel() . '", region "' . $importTargetsEntity->getRegionLevel() . '", period "' . $importTargetsEntity->getPeriod() . '".'
                    );
                }

                // - isChanged
                $fixedComparativeCriteria = array(
                    'clientOutputId' => $importTargetsEntity->getClientOutputId(),
                    'productMarketLevel' => $importTargetsEntity->getProductMarketLevel(),
                    'regionLevel' => $importTargetsEntity->getRegionLevel(),
                    'period' => $importTargetsEntity->getPeriod()
                );
                $changeableCriteria = array(
                    'targetUnits' => $importTargetsEntity->getTargetUnits(),
                    'targetValue' => $importTargetsEntity->getTargetValue(),
                    'msUnitsTarget' => $importTargetsEntity->getMsUnitsTarget(),
                    'msValueTarget' => $importTargetsEntity->getMsValueTarget()
                );
                if ($this->get('app.changed_data')->isChanged($currentRepository, $fixedComparativeCriteria, $changeableCriteria)) {
                    $importTargetsEntity->setTargetStatus('CHANGED');
                    // Data quality check
                    $dataQualityChecksArray[] = array(
                        'clientOutputId' => $importTargetsEntity->getClientOutputId(),
                        'status' => 'CHANGED TARGET',
                        'info' => 'Units or value has been changed for the following target: product market "' . $importTargetsEntity->getProductMarketLevel() . '", region "' . $importTargetsEntity->getRegionLevel() . '", period "' . $importTargetsEntity->getPeriod() . '".'
                    );
                }

                // - isUnchanged
                if ($this->get('app.unchanged_data')->isUnchanged($currentRepository, array(
                    'clientOutputId' => $importTargetsEntity->getClientOutputId(),
                    'productMarketLevel' => $importTargetsEntity->getProductMarketLevel(),
                    'regionLevel' => $importTargetsEntity->getRegionLevel(),
                    'period' => $importTargetsEntity->getPeriod(),
                    'targetUnits' => $importTargetsEntity->getTargetUnits(),
                    'targetValue' => $importTargetsEntity->getTargetValue(),
                    'msUnitsTarget' => $importTargetsEntity->getMsUnitsTarget(),
                    'msValueTarget' => $importTargetsEntity->getMsValueTarget()
                ))) {
                    $importTargetsEntity->setTargetStatus('UNCHANGED');
                }

                // - isDuplicate
                if ($this->get('app.duplicate_data')->isDuplicate($importRepository, array(
                    'clientOutputId' => $importTargetsEntity->getClientOutputId(),
                    'productMarketLevel' => $importTargetsEntity->getProductMarketLevel(),
                    'regionLevel' => $importTargetsEntity->getRegionLevel(),
                    'period' => $importTargetsEntity->getPeriod()
                ))) {
                    $duplicateImportTargetsEntitiesArray[] = $importTargetsEntity;
                    // Data quality check
                    $dataQualityChecksArray[] = array(
                        'clientOutputId' => $importTargetsEntity->getClientOutputId(),
                        'status' => 'WARNING',
                        'info' => 'Duplicate target: product market "' . $importTargetsEntity->getProductMarketLevel() . '", region "' . $importTargetsEntity->getRegionLevel() . '", period "' . $importTargetsEntity->getPeriod() . '".'
                    );
                }

                $em->persist($importTargetsEntity);
            }
            $em->flush();

            // On parcourt les current targets pour voir s'il y en a qui vont être supprimées
            // Si oui, on les stocke dans un array
            $removedCurrentTargetsEntitiesArray = array();
            $importClientoutputids = $importRepository->getDistinctClientOutputId();
            foreach ($importClientoutputids as $importClientoutputid) {
                $currentTargetsEntities = $currentRepository->findBy($importClientoutputid);
                foreach ($currentTargetsEntities as $currentTargetsEntity) {
                    // isRemoved
                    if ($this->get('app.removed_data')->isRemoved($importRepository, array(
                        'clientOutputId' => $currentTargetsEntity->getClientOutputId(),
                        'productMarketLevel' => $currentTargetsEntity->getProductMarketLevel(),
                        'regionLevel' => $currentTargetsEntity->getRegionLevel(),
                        'period' => $currentTargetsEntity->getPeriod()
                    ))) {
                        $removedCurrentTargetsEntitiesArray[] = $currentTargetsEntity;
                        // Data quality check
                        $dataQualityChecksArray[] = array(
                            'clientOutputId' => $currentTargetsEntity->getClientOutputId(),
                            'status' => 'REMOVED TARGET',
                            'info' => 'Following target has been removed: product market "' . $currentTargetsEntity->getProductMarketLevel() . '", region "' . $currentTargetsEntity->getRegionLevel() . '", period "' . $currentTargetsEntity->getPeriod() . '".'
                        );
                    }
                }
            }

            // On récupère:
            // - unexpected
            $unexpectedImportTargetsEntitiesArray = $importRepository->findBy(array('targetStatus' => 'UNEXPECTED'));
            // - new
            $newImportTargetsEntitiesArray = $importRepository->findBy(array('targetStatus' => 'NEW'));
            // - changed
            $changedImportTargetsEntitiesArray = $importRepository->findBy(array('targetStatus' => 'CHANGED'));
            // - unchanged
            $unchangedImportTargetsEntitiesArray = $importRepository->findBy(array('targetStatus' => 'UNCHANGED'));
            // - removed
            //$removedCurrentTargetsEntitiesArray
            // - duplicates (currently no implemented for targets)
            //$duplicateImportTargetsEntitiesArray

            // On insère les DQC dans la table DQC
            foreach ($dataQualityChecksArray as $dataQualityCheckArray) {
                $dataQualityCheck = new TarDataQualityChecks();
                $dataQualityCheck->setClientOutputId($dataQualityCheckArray['clientOutputId']);
                $dataQualityCheck->setLoadDate($currentLoadDate);
                $dataQualityCheck->setStatus($dataQualityCheckArray['status']);
                $dataQualityCheck->setInfo($dataQualityCheckArray['info']);
                $em->persist($dataQualityCheck);
            }

            $em->flush();


            // On affiche le résultat:
            // - warnings (unexpected, missing, duplicates)
            // - removed
            // - changed
            // - new
            // - unchanged

            // --------------------------------------------------------------
            /*
            if (isset($unexpectedResults)) {
                return $this->render('Targets/test.html.twig', array(
                    'results' => $unexpectedResults//$dwhValue->getProductMarketLevel(),
                ));
            }
            */
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
            'importForm' => $importFileForm->createView(),
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
        $importRepository = $em->getRepository('AppBundle:TarImportTargets');
        $currentRepository = $em->getRepository('AppBundle:TarCurrentTargets');
        $dqcRepository = $em->getRepository('AppBundle:TarDataQualityChecks');

        $dataQualityChecks = $dqcRepository->findBy(array('loadDate' => $loadDate),array('id' => 'asc'), null, null);
        $importTargets = $importRepository->findAll();

        // Number of distinct clientoutputID
        $dqcClientoutputids = $dqcRepository->getDistinctClientOutputId($loadDate);

        // Status by ClientoutputId
        $statusByClientoutputId = array();
        foreach ($dqcClientoutputids as $dqcClientoutputid) {
            //$clientoutputId = $dqcClientoutputids['clientOutputId'];
            $statusByClientoutputId[] = array(
                'clientoutputId' => $dqcClientoutputid['clientOutputId'],
                'nbr_of_warnings' => count($dqcRepository->findBy(array('clientOutputId' => $dqcClientoutputid['clientOutputId'], 'loadDate' => $loadDate, 'status' => 'WARNING'))),
                'nbr_of_removed_data' => count($dqcRepository->findBy(array('clientOutputId' => $dqcClientoutputid['clientOutputId'], 'loadDate' => $loadDate, 'status' => 'REMOVED TARGET'))),
                'nbr_of_changed_data' => count($importRepository->findBy(array('clientOutputId' => $dqcClientoutputid['clientOutputId'], 'targetStatus' => 'CHANGED'))),
                'nbr_of_new_data' => count($importRepository->findBy(array('clientOutputId' => $dqcClientoutputid['clientOutputId'], 'targetStatus' => 'NEW'))),
                'nbr_of_unchanged_data' => count($importRepository->findBy(array('clientOutputId' => $dqcClientoutputid['clientOutputId'], 'targetStatus' => 'UNCHANGED'))),
                'nbr_of_current_data' => count($currentRepository->findBy(array('clientOutputId' => $dqcClientoutputid['clientOutputId']))),
                'nbr_of_import_data' => count($importRepository->findBy(array('clientOutputId' => $dqcClientoutputid['clientOutputId'])))
            );
        }

        // Overview status
        $overviewStatus = array(
            'total_nbr_of_warnings' => count($dqcRepository->findBy(array('loadDate' => $loadDate, 'status' => 'WARNING'))),
            'total_nbr_of_removed_data' => count($dqcRepository->findBy(array('loadDate' => $loadDate, 'status' => 'REMOVED TARGET'))),
            'total_nbr_of_changed_data' => count($importRepository->findBy(array('targetStatus' => 'CHANGED'))),
            'total_nbr_of_new_data' => count($importRepository->findBy(array('targetStatus' => 'NEW'))),
            'total_nbr_of_unchanged_data' => count($importRepository->findBy(array('targetStatus' => 'UNCHANGED')))
        );


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
                while ($dqcRepository->findOneBy(array('loadDate' => $loadDate))) {
                    $itemsToRemove = $dqcRepository->findOneBy(array('loadDate' => $loadDate));
                    $em->remove($itemsToRemove);
                    $em->flush();
                }
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
                //$em = $this->getDoctrine()->getManager(); // TO DELETE IF EVERYTHING WORKS
                //$currentRepository = $em->getRepository('AppBundle:TarCurrentTargets'); // TO DELETE IF EVERYTHING WORKS
                //$importRepository = $em->getRepository('AppBundle:TarImportTargets'); // TO DELETE IF EVERYTHING WORKS

                $distinctClientoutputidInImportTargets = $importRepository->getDistinctClientOutputId();

                for ($row = 0; $row < count($distinctClientoutputidInImportTargets); $row++)
                {
                    $currentImportClientoutputid = $distinctClientoutputidInImportTargets[$row]['clientOutputId']; // Current Client_output_id
                    while ($currentRepository->findOneBy(array('clientOutputId' => $currentImportClientoutputid)))
                    {
                        $itemsToRemove = $currentRepository->findOneBy(array('clientOutputId' => $currentImportClientoutputid));
                        $em->remove($itemsToRemove);
                        $em->flush();
                    }
                }

                // Adding new mappings
                //$em = $this->getDoctrine()->getManager(); // TO DELETE IF EVERYTHING WORKS
                //$currentRepository = $em->getRepository('AppBundle:TarCurrentTargets'); // TO DELETE IF EVERYTHING WORKS
                //$importRepository = $em->getRepository('AppBundle:TarImportTargets'); // TO DELETE IF EVERYTHING WORKS
                //$importTargets = $importRepository->findAll(); // TO DELETE IF EVERYTHING WORKS
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
            'dataQualityChecks' => $dataQualityChecks,
            'importTargets' => $importTargets,
            'statusByClientoutputId' => $statusByClientoutputId,
            'overviewStatus' => $overviewStatus,
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
