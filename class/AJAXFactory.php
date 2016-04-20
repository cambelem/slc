<?php
namespace slc;

class AJAXFactory {
	static private $calls;
	
	private $call;
	private $data; // map
	private $result;
	
	
	private function __construct() {
		
	}
	
	static public function get($call=0) {
		if ( !isset($calls[$call]) )
			$calls[$call] = new AJAXFactory();
		
		return $calls[$call];
	}
	
	public function loadCall($action) {
		
		$file = $action.'.php';
		
		if ( !file_exists( '\\mod\\slc\\class\\ajax\\'.$file ) ) {
            throw new \slc\exceptions\AJAXNotFoundException( "Could not find AJAX file '$file'" );
        }
        
		if ( !class_exists( "\\slc\\ajax\\".$action ) ) {
            throw new \slc\exceptions\AJAXNotFoundException( "No AJAX class '$action' located" );
        }
        $action = "\\slc\\ajax\\".$action;
        $this->call = new $action();
	}
	
	public function setData($data) {
		$this->call->setData($data);
	}
	
	public function execute() {
		$this->call->execute();
	}
	
	public function result() {
		$this->result = $this->call->getResult();
		return $this->result;
	}
	
	
}

 
