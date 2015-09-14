<?php

function REPORTstudentsseen() {
    $query = "SELECT "; 
    $query .= "(SELECT COUNT(DISTINCT id) FROM slc_visit) AS visits, ";
    $query .= "(SELECT COUNT(DISTINCT id) FROM slc_client) AS clients, ";
    $query .= "(SELECT COUNT(DISTINCT id) FROM slc_issue) AS issues, ";
    $query .= "(SELECT SUM(counter) FROM slc_visit_issue_index) AS followups ";
    
    $db = new \PHPWS_DB();
    //$db->setTestMode();
    $results = $db->select(null, $query);
    
    if(\PHPWS_Error::logIfError($results)){
        throw new \slc\exceptions\DatabaseException();
    }
    
    $results = $results[0]; // since it's a count, should always return a number
    $visits = $results['visits'];
    $clients = $results['clients'];
    $issues = $results['issues'];
    $followups = $results['followups'] - $issues; // Every issue has at least one visit, so remove initial
    
    $bgcolor = array(0=>"#FFFFFF",1=>"#FFEC8B");
    $r = 0;
    
    $html = "";
    $html .= "<table class='reportTable'>";
    $html .= "<tr><th style='width:150px;'>Situation</th><th>Record</th></tr>";
    $html .= "<tr style='background-color:$bgcolor[$r];'><td>Total Visits</td><td>".$visits."</td></tr>"; $r = !$r;
    $html .= "<tr style='background-color:$bgcolor[$r];'><td>Total Clients</td><td>".$clients."</td></tr>"; $r = !$r;
    $html .= "<tr style='background-color:$bgcolor[$r];'><td>Total Issues</td><td>".$issues."</td></tr>"; $r = !$r;
    $html .= "<tr style='background-color:$bgcolor[$r];'><td>Visits per Client</td><td>".(round($visits/$clients,2))."</td></tr>"; $r = !$r;
    $html .= "<tr style='background-color:$bgcolor[$r];'><td>Issues per Visit</td><td>".(round($issues/$visits,2))."</td></tr>"; $r = !$r;
    $html .= "<tr style='background-color:$bgcolor[$r];'><td>Followups per Issue</td><td>".(round($followups/$issues, 2))."</td></tr>"; $r = !$r;
    $html .= "</table>";
    
    return $html;
}

?>