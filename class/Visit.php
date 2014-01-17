<?php

class Visit {
	// database variables
	public $id;
	public $initial_date;
	
	
	// useful one
	public $client_id;
	public $issues = array();
	
	public function addIssue(Issue $issue) {
		$this->issues[$issue->id] = $issue;
	}
}

?>