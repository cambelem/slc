<?php
namespace slc\views;

class ViewReports extends View {
	public function display(\slc\CommandContext $context) {
		\javascriptMod('slc', 'report');
		$HTMLcontent = "";
                /*
                $content = array();		
		
		
		// Report Dropdown
		$ajax = \slc\AjaxFactory::get("report_picker");
		$ajax->loadCall("GETReportBox");
		$ajax->execute();
		$result = $ajax->result(); 
		$content['REPORTPICKER'] = $result['report_picker']; // the HTML for the picker
                */
        // Date range selection
        javascript('datepicker');


        
        $form = new \PHPWS_Form("timespan");

        $form->addText('start_date', '');   // date pickers are initially blank
        $form->setSize('start_date', 10);
        $form->addCssClass('start_date', 'datepicker');
        $form->addCssClass('start_date', 'form-control');

        $form->addText('end_date', '');     // date pickers are initially blank
        $form->setSize('end_date', 10);
        $form->addCssClass('end_date', 'datepicker');
        $form->addCssClass('end_date', 'form-control');

        $content['START_DATE'] = $form->get('start_date');
        $content['END_DATE'] = $form->get('end_date');

        // Process the template
        $HTMLcontent .= \PHPWS_Template::process($content, 'slc', 'Report.tpl');
		
        return parent::useTemplate($HTMLcontent); // Insert into the accessible div
	}
}
?>
