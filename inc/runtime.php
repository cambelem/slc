<?php
namespace slc;

if (\PHPWS_Core::atHome() && \Current_User::isLogged()) {
    $path = $_SERVER['SCRIPT_NAME'].'?module=slc';

    header('HTTP/1.1 303 See Other');
    header("Location: $path");
    exit();
}

?>
