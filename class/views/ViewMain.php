<?php

class ViewMain extends slc\View {
	 public function display(\CommandContext $context) {

	 	$form = new PHPWS_Form('client_id_entry');
	 	$form->addHidden('module', 'slc');
	 	$form->addHidden('view','Client');
	 	//$form->addHidden('action','GETClientData');
	 	$form->addText('banner_id');
	 	$form->setLabel('banner_id', 'Enter Client ID: ');
	 	$form->addSubmit('View Issues');

	 	$tpl = $form->getTemplate();
	 	
	 	$content = PHPWS_Template::process($tpl, 'slc', 'Main.tpl');
	 	 	
	 	return parent::useTemplate($content); // Insert into the accessible div
	 }
}

?>
