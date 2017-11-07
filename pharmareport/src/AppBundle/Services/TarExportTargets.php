<?php

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\StreamedResponse; // used for the export

class TarExportTargets
{
    public function exportTargets($clientoutputId, $repository)
    {
        $fileName = 'PharmaReport_Targets_' . $clientoutputId . '.csv';

        $response = new StreamedResponse(function() use($clientoutputId, $repository) {

            $targets = $repository->findBy(array('clientOutputId' => $clientoutputId));
            $handle = fopen('php://output', 'r+');
            $headersArray = array('ClientoutputId', 'Product Market Level', 'Region Level', 'Period Type', 'Period', 'Target units', 'MS Units Target', 'MS Value Target', 'Target Value');
            fputcsv($handle, $headersArray, ';');

            foreach ($targets as $target) {
                fputcsv(
                    $handle,
                    array(
                      $target->getClientOutputId(),
                      $target->getProductMarketLevel(),
                      $target->getRegionLevel(),
                      $target->getPeriodType(),
                      $target->getPeriod(),
                      $target->getTargetUnits(),
                      $target->getMsUnitsTarget(),
                      $target->getMsValueTarget(),
                      preg_replace('/\s+/', '', $target->getTargetValue())
                    ),
                    ";"
                );
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $fileName));
        //$response->headers->set('Content-Disposition','attachment; filename="PharmaReport_Targets.csv"');

        return $response;
    }
}
