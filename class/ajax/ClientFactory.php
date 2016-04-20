<?php
namespace slc\ajax;

class ClientFactory 
{
	// Saves the client to the database
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

	// Grabs the client from the database by their encrypted banner id.
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

		// If the client is not in the database, return false.
		if ($client === false)
		{
			return null;
		}
		else
		{
			// Associate the client with their name here.
			$client->setFirstName($fname);
	   	 	$client->setLastName($lname);
	   	 	$client->setName($fullName);
		}

		return $client;
		
	}

	// Grabs the student data from the database 
	// (Don't confuse a student with a client)
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

	// Grabs the referral type that the client has set up.
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

 