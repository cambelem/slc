<?php

namespace slc\ajax;

class ClientFactory 
{
	
	public static function saveClient($client)
	{
		$db = \Database::newDB();
		$pdo = $db->getPDO();

        $values = array('id'=>$client->getId(),
						'fv'=>$client->getFirstVisit(),
						'classification'=>$client->getClassification(),
						'll'=>$client->getLivingLocation(),
						'major'=>$client->getMajor(),
						'referral'=>-1);

        $query = 'INSERT INTO slc_client (id, first_visit, classification, living_location, major, referral)
        		  VALUES (:id, :fv, :classification, :ll, :major, :referral)';

	  	$sth = $pdo->prepare($query);
		$sth->execute($values);
	}

	public static function getClientByEncryptedId($eBannerId) //$client, 
	{
		$db = \Database::newDB();
		$pdo = $db->getPDO();

		$query = "SELECT *
				  FROM slc_client
				  WHERE id = :bannerId";

		$sth = $pdo->prepare($query);
		$sth->execute(array('bannerId'=>$bannerId));
		$result = $sth->fetchObject('\slc\ClientDB');

		return $result;

	}

	public static function getClientByEncryBanner($eBannerId, $fname, $lname, $fullName)
	{
		$db = \Database::newDB();
		$pdo = $db->getPDO();

		$query = 'SELECT * 
				  FROM slc_client
				  WHERE id = :eBannerId';

		$sth = $pdo->prepare($query);
		$sth->execute(array('eBannerId'=>$eBannerId));
		$client = $sth->fetchObject('\slc\ClientDB');
		//var_dump($result);
		//exit;
		if ($client === false)
		{
			return null;
		}
		else
		{
			$client->setFirstName($fname);
	   	 	$client->setLastName($lname);
	   	 	$client->setName($fullName);
		}

		return $client;
		
	}

	public static function getVisitsByClientId($clientId)
	{

        $db = \Database::newDB();
		$pdo = $db->getPDO();

		$query = 'SELECT id, initial_date 
				  FROM slc_visit 
				  WHERE client_id = :clientId';

		$sth = $pdo->prepare($query);
		$sth->execute(array('clientId'=>$clientId));
		$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
	}

	public static function getClientByBannerId($bannerId)
	{
		$db = \Database::newDB();
		$pdo = $db->getPDO();

		$query = "SELECT *
				  FROM slc_student_data
				  WHERE id = :bannerId";

		$sth = $pdo->prepare($query);
		$sth->execute(array('bannerId'=>$bannerId));
		$result = $sth->fetchObject('\slc\ClientDB');

		return $result;
	}

	public static function getReferralType($cReferral)
	{
		$db = \Database::newDB();
		$pdo = $db->getPDO();

		$query = 'SELECT * 
    			  FROM slc_referral_type 
    			  WHERE id= :cReferral';
        
        $sth = $pdo->prepare($query);
		$sth->execute(array('cReferral'=>$cReferral));
		$result = $sth->fetchAll(\PDO::FETCH_ASSOC);

		return $result;
	}
}

?>