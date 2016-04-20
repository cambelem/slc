<?php
namespace slc;

// Ensure core directory is defined
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

// Ensure login
\Current_User::requireLogin();

\PHPWS_Core::requireInc('slc', 'functions.php');

\Layout::addStyle('slc');

// Javascript
javascript('jquery_ui');
\javascriptMod('slc', 'json');


$context = new CommandContext();
$content = '';

// Extract the action from the context
if ($context->has('action'))
{
    $action = $context->get('action');
    $af = AJAXFactory::get();
    $af->loadCall($action);
    $af->execute();

    \NQ::close();

    echo json_encode($af->result()); // To be left on the page for consumption
    exit(); // Kill it
}
else
{
	// No AJAX found; do nothing
    $action = 'None';
}

// Extract the view from the context
if ($context->has('view'))
{
    $view = $context->get('view');
}
else
{
    $view = 'Main';
}

$nv = new \slc\NotificationView();
$notifications = $nv->display();
\Layout::add($notifications);

// Get content from view
$view = ViewFactory::getView($view);
$content = $view->display($context);

\Layout::add($content);


// Add the top bar
$navLinks = new \slc\views\NavLinks();
\Layout::plug($navLinks->display(), 'NAV_LINKS');

 
