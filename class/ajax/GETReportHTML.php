<?php
namespace slc\ajax;

class GETReportHTML extends AJAX {

    public function execute()
    {    
		$reportName = $_REQUEST['report_type'];
    	$startDate = strtotime($_REQUEST['startDate']);
        $endDate = strtotime($_REQUEST['endDate']) + 86400;

    	$report = new \slc\reports\RunReport();
    	$content = $report->execute($startDate, $endDate, $reportName); 
		
		$tpl = $content->getHtmlView();

        $this->addResult("__html", $tpl);	      
    }
}

