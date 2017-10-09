<?php

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\StreamedResponse; // used for the export

class GsrmExportMapping
{
    public function exportMapping($clientoutputId, $repository)
    {
        $response = new StreamedResponse(function() use($clientoutputId, $repository) {

            $geoSalesRepMappings = $repository->findBy(array('clientOutputId' => $clientoutputId));
            $handle = fopen('php://output', 'r+');
            $headersArray = array('ClientoutputId', 'Version geo structure code', 'MarketId', 'Level', 'NameLevel', 'First Name', 'Last Name', 'E-mail');
            fputcsv($handle, $headersArray, ';');

            foreach ($geoSalesRepMappings as $geoSalesRepMapping) {
                fputcsv(
                    $handle,
                    array_map('utf8_decode',array(
                      $geoSalesRepMapping->getClientOutputId(),
                      $geoSalesRepMapping->getVersionGeoStructureCode(),
                      $geoSalesRepMapping->getGeoTeam(),
                      $geoSalesRepMapping->getGeoLevelNumber(),
                      $geoSalesRepMapping->getGeoValue(),
                      $geoSalesRepMapping->getSrFirstName(),
                      $geoSalesRepMapping->getSrLastName(),
                      preg_replace('/\s+/', '', $geoSalesRepMapping->getSrEmail())
                    )),
                    ";"
                );
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition','attachment; filename="PharmaReport_Geo_SalesRep_Mapping.csv"');

        return $response;
    }
}
