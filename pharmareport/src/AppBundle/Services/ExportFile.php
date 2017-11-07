<?php

namespace AppBundle\Services;

use AppBundle\Encoding;

class ExportFile
{

    public function exportCSV($filename)
    {

        $result = array();

        // ==========================================================================
        // VALIDATIONS
        // ==========================================================================

        // File exists ?
        if (!(file_exists($file))) {
            $result = array(
                'error' => 'File ' . $file . ' does not exist'
            );
            return $result;
        }

        // File format = CSV ?

        // File openable ?
        if (fopen($file, "r") == FALSE) {
            $result = array(
                'error' => 'Impossible to open the file ' . $file
            );
            return $result;
        }

        // Correct number of columns?
        $handle = fopen($file, "r");
        $row = 0;
        while (($data = fgetcsv($handle, 1500, ";")) !== FALSE) // $data = array containing data of one row
        {
            $numberItemsOnRow = count($data);
            if ($numberItemsOnRow != $expectedNumberOfColumns) {
                $error_row = $row+1;
                $result = array(
                    'error' => 'Row <strong>' . $error_row . '</strong> : unexpected number of items.<br />' . $numberItemsOnRow . ' items found, ' . $expectedNumberOfColumns . ' items (columns) are expected.'
                );
                return $result;
            }
            $row++;
        }
        fclose($handle);

        // Correct data types?


        // ==========================================================================
        // IMPORT DATA
        // ==========================================================================
        $handle = fopen($file, "r");
        $row = 0;

        while (($data = fgetcsv($handle, 1500, ";")) !== FALSE) // $data = array containing data of one row
        {
            $result[] = Encoding::toUTF8($data);
        }
        fclose($handle);

        return $result;
    }
}
