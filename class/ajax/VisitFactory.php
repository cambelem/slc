<?php

namespace slc\ajax;

class VisitFactory 
{
	
	public static function getVisitByClientId($clientId)
	{

        $db = \Database::newDB();
		$pdo = $db->getPDO();

		$query = 'SELECT id, initial_date 
				  FROM slc_visit 
				  WHERE client_id = :clientId';

		$sth = $pdo->prepare($query);
		$sth->execute(array('clientId'=>$clientId));
		$result = $sth->fetchAll(\PDO::FETCH_ASSOC);

		return $result;
	}

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
		//fetchObject('\slc\VisitDB');
		return $result;
	}

		public static function saveVisit($visit)
	{
		$db = new \PHPWS_DB("slc_visit");
		$results = $db->saveObject($visit);
        
		if(\PHPWS_Error::logIfError($results)){
            throw new \slc\exceptions\DatabaseException();
        }

        return $results;
	}
}

?>