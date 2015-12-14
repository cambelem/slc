<?php
namespace slc\reports;

abstract class Report {


	public function __construct();

	public function execute();

	public function getHtmlView();

	public function getCsvView();




	
}

?>
}

?>