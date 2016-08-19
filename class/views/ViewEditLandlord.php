<?php 
namespace slc\views;

class ViewEditLandlord extends View {
	public function display(\slc\CommandContext $context) {

		$HTMLcontent = "";
	 	$content = array();

		// \javascriptMod('slc', 'editLandlord');
		// javascript('jquery');
		$content['source_http'] = PHPWS_SOURCE_HTTP;
	 	$HTMLcontent .= \PHPWS_Template::process($content, 'slc', 'EditLandlords.tpl');
	 	
	 	return parent::useTemplate($HTMLcontent); // Insert into the accessible div

	}
}
 
