<?php
namespace slc\ajax;

class GETNewIssue extends AJAX {

    public function execute() {

		// Grabs the data associated with each problem type as well as their
		// generic data.
		$landlordTentant = $this->grabProblemTypes('Landlord-Tenant', 997);
		$conditions 	 = $this->grabProblemTypes('Conditions', 996);
		$other 			 = $this->grabProblemTypes('Problem', 999);
		$criminal 		 = $this->grabProblemTypes(null, 998);

		$tree = array_merge($landlordTentant, $conditions,$other, $criminal);

		$landlords = LandlordFactory::getLandlords();

		$this->addResult("tree", $tree);
		$this->addResult("landlords", $landlords);
	}
	
	private function grabProblemTypes($type, $id) {

		// Grabs info about the landlord-tenant and the generic
		// landlord-tenant
		if ($type == null)
			$type = 'Criminal';



		$db = \Database::newDB();
		$pdo = $db->getPDO();

		$query = "SELECT id, type, description 
				  FROM slc_problem 
				  WHERE id = :id 
				  OR type = :name";

		$sth = $pdo->prepare($query);
		$sth->execute(array('name'=>$type, 'id'=>$id));
		$result = $sth->fetchAll(\PDO::FETCH_ASSOC);

		if ($type == 'Problem')
			$type = 'Other';
		else if($type == 'Landlord-Tenant')
			$type = 'LandlordTenant';

		$typeArray = array();
		foreach ($result as $r) {
			$typeArray[$type][] = array("problem_id" => $r['id'], "name"=>$r['description'], "type"=>$r['type']);      	
        }
	
		return $typeArray;
	}

}

 
