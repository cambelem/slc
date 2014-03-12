<?php

// Ensure core directory is defined
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

// Ensure login
Current_User::requireLogin();


// Initialize required classes

// Objects
PHPWS_Core::initModClass('slc', 'Client.php');
PHPWS_Core::initModClass('slc', 'Visit.php');
PHPWS_Core::initModClass('slc', 'Issue.php');

// Indexes
PHPWS_Core::initModClass('slc', 'indexes/VisitIssue.php');
PHPWS_Core::initModClass('slc', 'indexes/VisitClient.php');

// Interfaces
PHPWS_Core::initModClass('slc', 'ajax/AJAX.php');
PHPWS_Core::initModClass('slc', 'views/View.php');
PHPWS_Core::initModClass('slc', 'views/NavLinks.php');

// Factories
PHPWS_Core::initModClass('slc', 'AJAXFactory.php');
PHPWS_Core::initModClass('slc', 'ViewFactory.php');

// Other
PHPWS_Core::initModClass('slc', 'CommandContext.php');
PHPWS_Core::initModClass('slc', 'Exceptions.php');
PHPWS_Core::requireInc('slc', 'functions.php');

Layout::addStyle('slc');

// Javascript
javascript('jquery_ui');
//javascript('modules/slc/json/');
javascriptMod('slc', 'json');
//javascriptMod('slc', 'jquerytools');


$context = new CommandContext();
$content = '';

// Extract the action from the context
try {
    $action = $context->get('action');
    $af = AJAXFactory::get();
    $af->loadCall($action);
    $af->execute();
    echo json_encode($af->result()); // To be left on the page for consumption
    exit(); // Kill it
} catch(ParameterNotFoundException $e){
	// No AJAX found; do nothing
    $action = 'None';
}

// Extract the view from the context
try {
    $view = $context->get('view');
} catch(ParameterNotFoundException $e){
    $view = 'Main';
}

// Get content from view

$view = ViewFactory::getView($view);
$content = $view->display($context);

// Build the panel
PHPWS_Core::initModClass('controlpanel', 'Panel.php');
$panel = new PHPWS_Panel('slc_panel');

$tabs = array();
$tabs['client']    = array('title' => 'Client Interaction', 'link' => 'index.php?module=slc&view=Main', 'link_title' => 'Submit Client/Visit Information');
$tabs['report']    = array('title' => 'Reports', 'link' => 'index.php?module=slc&view=Reports', 'link_title' => 'Generate Reports');
$panel->quickSetTabs($tabs);

// Display the panel
$panel = $panel->display($content);
Layout::add($panel);

// Setup Styles
Layout::addStyle('controlpanel');

// Add the top bar
$navLinks = new NavLinks();
Layout::plug($navLinks->display(), 'NAV_LINKS');

?>
