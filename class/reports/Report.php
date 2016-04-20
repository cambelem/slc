<?php
namespace slc\reports;

abstract class Report {

	public $content;
	public $startDate;
	public $endDate;

	public function __construct($startDate, $endDate) 
	{
		$this->startDate = $startDate;
		$this->endDate = $endDate;
		$this->execute();
	}

	abstract function execute();

	abstract function getHtmlView();

}

 
