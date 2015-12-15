<?php
namespace slc\ajax;

class VisitFactory 
{	
	// Grabs the list of visits by the client's ID from the database. 
	public static function getVisitByCId($clientId)
	{
		$db = \Database::newDB();
		$pdo = $db->getPDO();

		$query = 'SELECT id, initial_date 
				  FROM slc_visit 
				  WHERE client_id = :clientId';

		$sth = $pdo->prepare($query);
		$sth->execute(array('clientId'=>$clientId));
		$result = $sth->fetchAll(\PDO::FETCH_CLASS, "\slc\VisitDB");

		return $result;
	}

	// Saves the visit to the database
	public static function saveVisit(&$pdo, $visit)
	{
        $values = array('initial_date'=>$visit->getInitialDate(),
						'client_id'=>$visit->getClientId());

        $query = 'INSERT INTO slc_visit (initial_date, client_id)
        		  VALUES (:initial_date, :client_id)';

	  	$sth = $pdo->prepare($query);
		$sth->execute($values);

        return $pdo->lastInsertId();
	}
}

 