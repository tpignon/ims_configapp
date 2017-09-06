<?php

namespace AppBundle\Services;

use AppBundle\Encoding;

class GsrmImportMapping
{

    public function importCSV($file)
    {

        if (!(file_exists($file))) {
            $error_row = $row+1;
            $geosalesrepMappings = array(
                'error_type' => 'file_does_not_exist',
                'error_file' => $file
            );
            return $geosalesrepMappings;
            //throw new \Exception('File "' . $file . '" doesn\'t exist.');
        }

        $geosalesrepMappings = array(); // This array will contain elements extracted from csv file

        // CSV file import
        if (($handle = fopen($file, "r")) !== FALSE)
        {
            $row = 0;
            $maxNumberItemsOnRow = '8';

            while (($data = fgetcsv($handle, 1500, ";")) !== FALSE) // $data = array containing data of one row
            {
                //$data = array_map("utf8_encode", $data); // to allow special character like accents in csv import file
                $numberItemsOnRow = count($data);

                if ($numberItemsOnRow != $maxNumberItemsOnRow) {
                    $error_row = $row+1;
                    $geosalesrepMappings = array(
                        'error_type' => 'nbr_items_on_row',
                        'error_row' => $error_row,
                        'error_nbr_of_columns' => $numberItemsOnRow,
                        'max_nbr_of_columns' => $maxNumberItemsOnRow
                    );
                    return $geosalesrepMappings;
                    //throw new \Exception('Error : too many columns in "' . $file . '". Number of columns is limited to ' . $maxNumberItemsOnRow . '. Row number : ' . $error_row);
                }

                $geosalesrepMappings[$row] = array(
                    'client_output_id' => $data[0],
                    'version_geo_structure_code' => $data[1],
                    'geo_team' => $data[2],
                    'geo_level_number' => $data[3],
                    'geo_value' => $data[4],
                    'sr_first_name' => Encoding::toUTF8($data[5]),//iconv(mb_detect_encoding($data[5]), 'ISO-8859-1//TRANSLIT//IGNORE', $data[5]),//mb_detect_encoding($data[5]), //$data[5],
                    'sr_last_name' => Encoding::toUTF8($data[6]),
                    'sr_email' => Encoding::toUTF8($data[7])
                );

                $row++;
            }
            fclose($handle);
        }

        return $geosalesrepMappings;
    }
}
