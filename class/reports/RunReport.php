<?php
namespace slc\reports;

class RunReport {

    public static function execute($startDate, $endDate,$reportName)
    {    
        //$file = 'SLC' . $this->reportName . '.csv';//'' . $startdate . 'to' . $enddate . '.csv';   // output filename


        switch($reportName)
        {
            case 'AppointmentStats':
            	$report = new ReportApptStats($startDate, $endDate);
                break;
            case 'IntakeProblemType':
            	$report = new ReportIntakePrbType($startDate, $endDate);
            	break;
            case 'LandlordTenant':
                $report = new ReportLandlordTen($startDate, $endDate);
                break;
            case 'ConditionByLandlord':
               	$report = new ReportCondByLandlord($startDate, $endDate);
                break;
            case 'ProblemByYear':
            	$report = new ReportPrbByYr($startDate, $endDate);
            	break;
            case 'TypeOfCondition':
            	$report = new ReportTypeOfCond($startDate, $endDate);
            	break;
            case 'TypeOfReferral':
            	$report = new ReportTypeOfRef($startDate, $endDate);
            	break;
            case 'LawByAgency':
            	$report = new ReportLawByAgency($startDate, $endDate);
            	break;   
        	default:
        		throw new \InvalidArgumentException("Unknown Report Name");         	            	
        }

        return $report;
    }
}

