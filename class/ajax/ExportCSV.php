<?php
/**
 * ExportCSV
 *
 * Class to download a CSV report to a user's browser.
 *
 * @author Chris Coley <chris at tux dot appstate dot edu>
 * @package SLC
 */
class ExportCSV extends AJAX {
    public function execute() {
        if (!isset($_REQUEST['report_type'])) {
            $this->addResult("msg", "No Report Type Supplied");
            return;
        }

        $csv;
        $startdate = $_REQUEST['start_date'];
        $enddate = $_REQUEST['end_date'];
        $file = 'SLC' . $_REQUEST['report_type'] . ' ' . $startdate . ' to ' . $enddate . '.csv';   // output filename

        $func = 'CSV' . $_REQUEST['report_type'];
        if (method_exists("ExportCSV", $func)) {
            $csv = call_user_func(array("ExportCSV", $func));
        } else {
            $csv = "The selected report is not available to be exported as a CSV file.";
            echo $csv;
            exit();
        }

        // Force the browser to open a 'save as' dialogue
        header('Content-Type: text/csv');
        header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Mon, 17 Sep 2012 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Content-Length: '.strlen($csv));
        header('Content-Disposition: attachment; filename="' . $file . '";');

        echo $csv;
        exit();
    }

    /**
     * Handles writing an array to a comma-separated string
     * 
     * @param Array $row Array of values to write
     * @param char $delimiter
     * @param char $enclosure
     * @param char $eol
     */
    private static function sputcsv(Array $row, $delimiter = ',', $enclosure = '"', $eol = "\n")
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
    
    /**
     * Handles converting the 'Appointment Statistics' report to a CSV file
     * Uses the same DB query as REPORTfollowupappts from GETReport.php
     */
    private function CSVfollowupappts() {
        $initialVisits  = 0;
        $clients        = 0;
        $issues         = 0;
        $followups      = 0;

        // Get date range from user
        $start_date = strtotime($_REQUEST['start_date']);
        $end_date = strtotime($_REQUEST['end_date']) + 86400;   // +1 day to make date range inclusive
        
        // Get the array of all visits whose initial visit happened in the time period. Equivalent to this query:
        // SELECT DISTINCT(id) FROM slc_visit WHERE initial_date >= $start_date AND initial_date < $end_date;
        $db = new PHPWS_DB('slc_visit');
        $db->addColumn('id', null, null, null, true);
        $db->addWhere('initial_date', $start_date, '>=');
        $db->addwhere('initial_date', $end_date, '<', 'AND');
        $visitIds = $db->select('col');

        // Get # of initial visits. Equivalent to this query: 
        // SELECT COUNT(DISTINCT(v_id)) FROM slc_visit_issue_index
        // WHERE v_id IN $visitIds;
        $db = new PHPWS_DB('slc_visit_issue_index');
        $db->addColumn('v_id', null, null, true, true);
        $db->addWhere('v_id', $visitIds, 'IN', 'AND');
        $initialVisits = $db->select('one');

        // Get the array of different 'counts' greater than 1. Equivalent to this query:
        // SELECT DISTINCT(counter) FROM slc_visit_issue_index WHERE counter>'1' ORDER BY counter DESC;
        $db->reset();
        $db->addColumn('counter', null, null, null, true);
        $db->addWhere('counter', '1', '>');
        $db->addOrder('counter desc');
        $counters = $db->select('col');

        //TODO: Make followups only count if they occured during the time period.
        // As of 05/20/2013 this is impossible due to the structure of the DB. We need to track when each followup occured.
        // For now we just count all followups for visits whose initial visit took place within the time period.

        // Calculate the number of followup visits.
        $visits = array();
        $db = new PHPWS_DB('slc_visit_issue_index');
        $db->addColumn('v_id', null, null, null, true);
        foreach ($counters as $count) {
            $db->addWhere('v_id', $visitIds, 'IN');

            // Make sure you don't count visits with multiple issues if they've already been counted.
            if (!empty($visits)) {
                $db->addWhere('v_id', $visits, 'NOT IN', 'AND');
            }

            $db->addWhere('counter', $count, '=', 'AND');
            $result = $db->select('col');
            $visits = $visits + $result;
            $followups += ($count-1) * count($result);
            $db->resetWhere();
        }

        // Get # of clients. Equivalent to this query:
        // SELECT COUNT(DISTINCT(id)) FROM slc_client
        // WHERE first_visit >= $start_date AND first_visit < $end_date;
        $db = new PHPWS_DB('slc_client');
        $db->addColumn('id', null, null, true, true);
        $db->addWhere('first_visit', $start_date, '>=');
        $db->addWhere('first_visit', $end_date, '<', 'AND');
        $clients = $db->select('one');

        // Get # of issues. Equivalent to this query: 
        // SELECT COUNT(DISTINCT(id)) FROM slc_issue
        // JOIN slc_visit_issue_index ON slc_issue.id = slc_visit_issue_index.i_id
        // WHERE slc_visit_issue_index.v_id IN $visitIds;
        $db = new PHPWS_DB('slc_issue');
        $db->addColumn('id', null, null, true, true);
        $db->addTable('slc_visit_issue_index', 'svii');
        $db->addWhere('slc_issue.id', 'svii.i_id');
        $db->addWhere('svii.v_id', $visitIds, 'IN', 'AND');
        $issues = $db->select('one');

        // Put the data into the CSV.
        $cols = array(  'Total Clients',
                        'Total Issues',
                        'Total Initial Visits',
                        'Total Followups',
                        'Issues per Visit (w/o Followups)',
                        'Visits per Client (w/o Followups)',
                        'Visits per Client (with Followups)',
                        'Followups per Issue',
                        'Followups per Visit');
        $rows = array(  $clients,
                        $issues,
                        $initialVisits,
                        $followups,
                        ($clients == 0 ? 0 : round($issues / $initialVisits, 2)),
                        ($clients == 0 ? 0 : round($initialVisits / $clients, 2)),
                        ($clients == 0 ? 0 : round(($initialVisits + $followups) / $clients, 2)),
                        ($clients == 0 ? 0 : round($followups / $issues, 2)),
                        ($clients == 0 ? 0 : round($followups / $initialVisits, 2)));
        $data = $this->sputcsv($cols);
        $data .= $this->sputcsv($rows);

        return $data;
    }

    /**
     * Handles converting the 'Landlord/Tenant' report to a CSV file
     * Uses the same DB query as REPORTlandlordtenant from GETReport.php
     */
    private function CSVlandlordtenant() {
        // Get date range from user
        $start_date = strtotime($_REQUEST['start_date']);
        $end_date = strtotime($_REQUEST['end_date']) + 86400;   // +1 day to make date range inclusive

        $landlords = "SELECT * FROM slc_landlord";
        $db = new PHPWS_DB();
        $landlords = $db->select(null, $landlords);
        $landlordnames = array();
        foreach( $landlords as $landlord )
            $landlordnames[] = $landlord['name'];

        $issues = "SELECT * FROM slc_problem WHERE tree LIKE '%Landlord-Tenant%' OR description LIKE 'Conditions' OR description LIKE 'Landlord-Tenant' "; // Covers generic landlord-tenant, too
        $db = new PHPWS_DB();
        $issues = $db->select(null, $issues);
        $issuenames = array();
        foreach( $issues as $issue )
            $issuenames[] = $issue['description'];


        // Get the issues listed ( all others 0 )
        $db = new PHPWS_DB();
        $db->addTable('slc_issue');
        $db->addTable('slc_problem');
        $db->addTable('slc_landlord');
        $db->addTable('slc_visit');
        $db->addTable('slc_visit_issue_index');
        $db->addColumn('slc_problem.description');
        $db->addColumn('slc_landlord.name');

        // 1st Join set
        // needs to be a left join to allow for landlord "not specified" which is recorded as NULL in DB
        $db->addJoin('left', 'slc_issue', 'slc_landlord', 'landlord_id', 'id');

        // 2nd Join set
        $db->addJoin('inner', 'slc_problem', 'slc_issue', 'id', 'problem_id');
        $db->addJoin('inner', 'slc_issue', 'slc_visit_issue_index', 'id', 'i_id');
        $db->addJoin('inner', 'slc_visit_issue_index', 'slc_visit', 'v_id', 'id');
        $db->addWhere('slc_visit.initial_date', $start_date, '>=');
        $db->addWhere('slc_visit.initial_date', $end_date, '<', 'AND');
        $results = $db->select();

        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }

        $theMatrix = $landlordnames;
        foreach($landlordnames as $lname) {
            $theMatrix[$lname] = $issuenames;
            foreach($issuenames as $iname)
                $theMatrix[$lname][$iname] = 0; // Populate with 0s 
        }


        foreach( $results as $r ) {
            // If landlord name is NULL, set as "Other / Unspecified"
            $name = isset($r['name']) ? $r['name'] : "Other / Unspecified";
            $description = $r['description'];

            if ( !array_key_exists($description,$theMatrix[$name]) )
                    $theMatrix[$name][$description] = 0;

            $theMatrix[$name][$description]++; // increment that value
        }

        // Create the final $data array
        $data = $this->sputcsv(array_merge(array_merge(array(' '), $issuenames), array('Landlord Totals')));

        $colTotals = array();
        foreach ($landlordnames as $landlord) {
            $row = array($landlord);
            $accumulator = 0;
            foreach ($issuenames as $issue) {
                $value = $theMatrix[$landlord][$issue];
                $row[] = $value;
                $accumulator += $value;
                if (!array_key_exists($issue, $colTotals)) {
                    $colTotals[$issue] = 0;
                }
                $colTotals[$issue] += $value;
            }
            $row[] = $accumulator;
            $data .= $this->sputcsv($row);
        }
        
        $totalRow = array('Issue Totals');
        $accumulator = 0;
        foreach ($issuenames as $issue) {
            if (array_key_exists($issue, $colTotals)) {
                $totalRow[] = $colTotals[$issue];
                $accumulator += $colTotals[$issue];
            }
        }
        $totalRow[] = $accumulator;
        $data .= $this->sputcsv($totalRow);

        return $data;
    }

    /**
     * Handles converting the 'Condition by Landlord' report to a CSV file
     * Uses the same DB query as REPORTconditionbylandlord from GETReport.php
     */
    private function CSVconditionbylandlord() {
        // Get date range from user
        $start_date = strtotime($_REQUEST['start_date']);
        $end_date = strtotime($_REQUEST['end_date']) + 86400;   // +1 day to make date range inclusive

        $landlords = "SELECT * FROM slc_landlord";
        $db = new PHPWS_DB();
        $landlords = $db->select(null, $landlords);
        $landlordnames = array();
        foreach( $landlords as $landlord )
            $landlordnames[] = $landlord['name'];

        $issues = "SELECT * FROM slc_problem WHERE type LIKE 'Conditions' "; // Covers generic landlord-tenant, too
        $db = new PHPWS_DB();
        $issues = $db->select(null, $issues);
        $issuenames = array();
        foreach( $issues as $issue )
            $issuenames[] = $issue['description'];
        $issuenames[] = "Conditions";

        // Get the issues listed ( all others 0 )
        $db = new PHPWS_DB();
        $db->addTable('slc_issue');
        $db->addTable('slc_problem');
        $db->addTable('slc_landlord');
        $db->addTable('slc_visit');
        $db->addTable('slc_visit_issue_index');
        $db->addColumn('slc_problem.description');
        $db->addColumn('slc_landlord.name');

        // 1st Join set
        // needs to be a left join to allow for landlord "not specified" which is recorded as NULL in DB
        $db->addJoin('left', 'slc_issue', 'slc_landlord', 'landlord_id', 'id');

        // 2nd Join set
        $db->addJoin('inner', 'slc_problem', 'slc_issue', 'id', 'problem_id');
        $db->addJoin('inner', 'slc_issue', 'slc_visit_issue_index', 'id', 'i_id');
        $db->addJoin('inner', 'slc_visit_issue_index', 'slc_visit', 'v_id', 'id');

        // WHERE (type LIKE "Conditions" OR description LIKE "Conditions") AND initial_date BETWEEN $start_date AND $end_date
        $db->addWhere('slc_problem.type', 'Conditions', 'LIKE', NULL, 'conditions');
        $db->addWhere('slc_problem.description', 'Conditions', 'LIKE', 'OR', 'conditions');
        $db->addWhere('slc_visit.initial_date', $start_date, '>=', 'AND');
        $db->addWhere('slc_visit.initial_date', $end_date, '<', 'AND');
        $results = $db->select();

        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }

        $theMatrix = $landlordnames;
        foreach($landlordnames as $lname) {
            $theMatrix[$lname] = $issuenames;
            foreach($issuenames as $iname)
                $theMatrix[$lname][$iname] = 0; // Populate with 0s 
        }

        foreach( $results as $r ) {
            // If landlord name is NULL, set as "Other / Unspecified"
            $name = isset($r['name']) ? $r['name'] : "Other / Unspecified";
            $description = $r['description'];

            $theMatrix[$name][$description]++; // increment that value
        }

        // Create the final $data array
        $data = $this->sputcsv(array_merge(array_merge(array(' '), $issuenames), array('Landlord Total')));

        $colTotals = array();
        foreach ($landlordnames as $landlord) {
            $row = array($landlord);
            $accumulator = 0;
            foreach ($issuenames as $issue) {
                $value = $theMatrix[$landlord][$issue];
                $row[] = $value;
                $accumulator += $value;
                if (!array_key_exists($issue, $colTotals)) {
                    $colTotals[$issue] = 0;
                }
                $colTotals[$issue] += $value;
            }
            $row[] = $accumulator;
            $data .= $this->sputcsv($row);
        }

        $totalRow = array('Issue Totals');
        $accumulator = 0;
        foreach ($issuenames as $issue) {
            if (array_key_exists($issue, $colTotals)) {
                $totalRow[] = $colTotals[$issue];
                $accumulator += $colTotals[$issue];
            }
        }
        $totalRow[] = $accumulator;
        $data .= $this->sputcsv($totalRow);

        return $data;
    }
}
?>
