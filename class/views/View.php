<?php

namespace slc\views;

abstract class View {
    abstract function display(\CommandContext $context);
    
    protected function useTemplate($content) {
    	
    	if (!isset($content)) {
    		throw new \slc\exceptions\TemplateContentNotDefinedException();
    	}
    	
    	$tpl = array();
    	$tpl['CONTENT'] = $content;
    		
    	return \PHPWS_Template::process($tpl, 'slc', 'Default.tpl');
    }

    protected $notifications;
    
    public function addNotifications($n)
    {
        $this->notifications = $n;
    }
}

 
