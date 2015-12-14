<?php
namespace slc\reports;

class CsvReport {
    /**
     * Handles writing an array to a comma-separated string
     * 
     * @param Array $row Array of values to write
     * @param char $delimiter
     * @param char $enclosure
     * @param char $eol
     */
    public function sputcsv(Array $row, $delimiter = ',', $enclosure = '"', $eol = "\n")
    {
        static $fp = false;
        if ($fp === false)
        {
            $fp = fopen('php://temp', 'r+'); // see http://php.net/manual/en/wrappers.php.php - yes there are 2 '.php's on the end.
            // NB: anything you read/write to/from 'php://temp' is specific to this filehandle
        }
        else
        {
            rewind($fp);
        }
    
        if (fputcsv($fp, $row, $delimiter, $enclosure) === false)
        {
            return false;
        }
    
        rewind($fp);
        $csv = fgets($fp);
    
        if ($eol != PHP_EOL)
        {
            $csv = substr($csv, 0, (0 - strlen(PHP_EOL))) . $eol;
        }
    
        return $csv;
    }
}
?>