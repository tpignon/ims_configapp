<?php

namespace AppBundle\Services;

class TarImportTargets
{
    public function importCSV($file)
    {

        if (!(file_exists($file))) {
            $error_row = $row+1;
            $importTargetsArray = array(
                'error_type' => 'file_does_not_exist',
                'error_file' => $file
            );
            return $importTargetsArray;
        }

        $importTargetsArray = array(); // This array will contain elements extracted from csv file

        // CSV file import
        if (($handle = fopen($file, "r")) !== FALSE)
        {
            $row = 0;
            $maxNumberItemsOnRow = '8';

            while (($data = fgetcsv($handle, 1500, ";")) !== FALSE) // $data = array containing data of one row
            {
                $data = array_map("utf8_encode", $data); // to allow special character like accents in csv import file
                $numberItemsOnRow = count($data);

                if ($numberItemsOnRow != $maxNumberItemsOnRow) {
                    $error_row = $row+1;
                    $importTargetsArray = array(
                        'error_type' => 'nbr_items_on_row',
                        'error_row' => $error_row,
                        'error_nbr_of_columns' => $numberItemsOnRow,
                        'max_nbr_of_columns' => $maxNumberItemsOnRow
                    );
                    return $importTargetsArray;
                }

                if (is_numeric($data[4])) {
                    $targetUnits = $data[4];
                } else {
                    $targetUnits = null;
                }

                if (is_numeric($data[5])) {
                    $msUnitsTarget = $data[5];
                } else {
                    $msUnitsTarget = null;
                }

                if (is_numeric($data[6])) {
                    $msValueTarget = $data[6];
                } else {
                    $msValueTarget = null;
                }

                if (is_numeric($data[7])) {
                    $targetValue = $data[7];
                } else {
                    $targetValue = null;
                }

                $importTargetsArray[$row] = array(
                    'client_output_id' => $data[0],
                    'product_market_level' => $data[1],
                    'region_level' => $data[2],
                    'period' => $data[3],
                    'target_units' => $targetUnits,
                    'ms_units_target' => $msUnitsTarget,
                    'ms_value_target' => $msValueTarget,
                    'target_value' => $targetValue
                );

                $row++;
            }
            fclose($handle);
        }

        return $importTargetsArray;
    }
}
