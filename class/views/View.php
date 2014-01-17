<?php

abstract class SLCView {
    abstract function display(CommandContext $context);
    
    protected function useTemplate($content) {
    	
    	if (!isset($content)) {
    		throw new TemplateContentNotDefinedException();
    	}
    	
    	$tpl = array();
    	$tpl['CONTENT'] = $content;
    		
    	return PHPWS_Template::process($tpl, 'slc', 'Default.tpl');
    }
}

?>
