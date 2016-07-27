<?php
namespace slc\ajax;

class LandlordFactory{

	public static function getLandlords()
	{

		$db = \Database::newDB();
		$pdo = $db->getPDO();

		$query = "SELECT * FROM slc_landlord ORDER BY name ASC";

		$sth = $pdo->prepare($query);
		$sth->execute();
		$result = $sth->fetchAll(\PDO::FETCH_ASSOC);

		return $result;
	}

	public static function saveLandlords($llName)
	{

		$db = \Database::newDB();
		$pdo = $db->getPDO();

		$query = "INSERT INTO slc_landlord (name)
				  VALUES(:name)";

		$sth = $pdo->prepare($query);
		$sth->execute(array('name'=> $llName));
	}

	public static function editLandlords($landlordData)
	{
		$id = $landlordData['id'];
		$name = $landlordData['name'];

		$db = \Database::newDB();
		$pdo = $db->getPDO();

		$query = "UPDATE slc_landlord
				  SET name = :name
				  WHERE id = :id";

		$sth = $pdo->prepare($query);
		$sth->execute(array('id' => $id, 'name' => $name));

	}

	public static function deleteLandlords()
	{

		// $db = \Database::newDB();
		// $pdo = $db->getPDO();

		// $query = "SELECT * FROM slc_landlord";

		// $sth = $pdo->prepare($query);
		// $sth->execute();
		// $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

		// return $result;
	}
}

 
