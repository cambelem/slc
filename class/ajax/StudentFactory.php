<?php

namespace slc\ajax;

class StudentFactory 
{

	public static function getStudentByBannerId($bannerId)
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

}

?>