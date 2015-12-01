<?php
namespace slc\ajax;

class IssuesFactory
{
	// Saves the issue into the database
	// returns the issue id
	public static function saveIssue($issue)
	{
		/*
		$db = \Database::newDB();
		$pdo = $db->getPDO();

        $values = array('id'=>$issue->getId(),
						'problem_id'=>$issue->getProblemId(),
						'landlord_id'=>$issue->getLandlordId());

        $query = 'INSERT INTO slc_issue (id, problem_id, landlord_id)
        		  VALUES (:id, :problem_id, :landlord_id)';

	  	$sth = $pdo->prepare($query);
		$sth->execute($values);
		*/
		$db = new \PHPWS_DB("slc_issue");
		$results = $db->saveObject($issue);
		return $results;
	}

	// Grabs the issues based off of the visit for a given person
	public static function getIssueByVisitId($vid)
	{
		$db = \Database::newDB();
		$pdo = $db->getPDO();

		$query = 'SELECT vii.id AS "VIIID", 
						p.description AS "ISSUENAME", 
						l.name as "LANDLORDNAME", 
						i.landlord_id as "LANDLORDID", 
						i.problem_id as "PROBLEMID", 
						vii.i_id AS "ISSUEID", 
						vii.counter AS "COUNTER", 
						vii.resolve_date AS "RESOLVEDATE", 
						vii.last_access AS "LASTACCESS"
				 FROM slc_visit_issue_index as vii
				 INNER JOIN slc_issue i ON vii.i_id=i.id
				 INNER JOIN slc_problem p ON i.problem_id=p.id
				 LEFT JOIN (slc_landlord l) ON (i.landlord_id = l.id)
				 WHERE vii.v_id = :vid';

		$sth = $pdo->prepare($query);
		$sth->execute(array('vid'=>$vid));
		$iresults = $sth->fetchAll(\PDO::FETCH_ASSOC);

		$issues = array();
		foreach( $iresults as $ir ) 
		{ 
        	$issue = new \slc\Issue($ir['ISSUEID']);
        	$issue->setName($ir['ISSUENAME']);
        	$issue->setLastAccess(prettyTime($ir['LASTACCESS'])." (".prettyAccess($ir['LASTACCESS']).")");	
			$issue->setCounter($ir['COUNTER']);
			$issue->setResolutionDate($ir['RESOLVEDATE']);
			$issue->setVisitIssueId($ir['VIIID']);
			$issue->setProblemId($ir['PROBLEMID']);
			$issue->setLandlordId($ir['LANDLORDID']);
			$issue->setLandlordName((($issue->getLandlordId() > 0)) ? $ir['LANDLORDNAME'] : null);
    		
    		$issues[] = $issue;

    	}
    
    	return $issues;
	}


}

?>