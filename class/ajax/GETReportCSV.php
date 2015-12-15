<?php
namespace slc\ajax;

class GETReportCSV extends AJAX {

    public function execute()
    {   
    	
    	$reportName = $_REQUEST['report_type'];
    	$startDate = strtotime($_REQUEST['startDate']);
    	$endDate = strtotime($_REQUEST['endDate']) + 86400;

        $report = new \slc\reports\RunReport();
    	$content = $report->execute($startDate, $endDate, $reportName); 
    	$csv = $content->getCsvView();	

		$file = 'SLC' . $reportName . '.csv'; 

    	// Force the browser to open a 'save as' dialogue
        header('Content-Type: text/csv');
        header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Mon, 17 Sep 2012 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Content-Length: '.strlen($csv));
        header('Content-Disposition: attachment; filename="' . $file . '"');

        echo $csv;
        exit();
    }
}

?>
