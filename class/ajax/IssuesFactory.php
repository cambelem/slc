<?php
namespace slc\ajax;

class IssuesFactory
{
	// Saves the issue into the database
	// returns the issue id
	public static function saveIssue($issue)
	{
		$db = new \PHPWS_DB("slc_issue");
		$results = $db->saveObject($issue);
		return $results;
	}

	// Grabs the issues based off of the visit for a given person
	public static function getIssueByVisitId($vid)
	{
		$db = \Database::newDB();
		$pdo = $db->getPDO();

		$query = 'SELECT issue.id AS "ISSUEID",
						p.description AS "ISSUENAME",
						l.name as "LANDLORDNAME",
						issue.landlord_id as "LANDLORDID",
						issue.problem_id as "PROBLEMID",
						issue.counter AS "COUNTER"
				 FROM slc_issue as issue
				 INNER JOIN slc_problem p ON issue.problem_id=p.id
				 LEFT JOIN (slc_landlord l) ON (issue.landlord_id = l.id)
				 WHERE issue.v_id = :vid';

		$sth = $pdo->prepare($query);
		$sth->execute(array('vid'=>$vid));
		$iresults = $sth->fetchAll(\PDO::FETCH_ASSOC);

		$issues = array();
		foreach( $iresults as $ir )
		{
        	$issue = new \slc\Issue();
			$issue->setId($ir['ISSUEID']);
			$issue->setVisitId($vid);
        	$issue->setName($ir['ISSUENAME']);
			$issue->setCounter($ir['COUNTER']);
			$issue->setProblemId($ir['PROBLEMID']);
			$issue->setLandlordId($ir['LANDLORDID']);
			$issue->setLandlordName((($issue->getLandlordId() > 0)) ? $ir['LANDLORDNAME'] : null);

    		$issues[] = $issue;

    	}

    	return $issues;
	}


}
