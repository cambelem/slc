<?php

namespace slc;
/*
 * CommandContext
 *
 * Command Context objects pass request variables to command objects and carry
 * errors back up to the view.
 *
 * This class is heavily based on the example from
 * "PHP Objects, Patterns, and Practice" by Matt Zandstra.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package mod
 * @subpackage slc
 */

class CommandContext {
    private $params = array();
    private $error = "";

    public function __construct() {
        $this->params = $_REQUEST;
    }

    public function addParam( $key, $val ) {
        $this->params[$key]=$val;
    }

    public function get( $key ) {
        return $this->params[$key];
    }

    public function has( $key ) {
        if (!isset($this->params[$key]))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public function setError( $error ) {
        $this->error = $error;
    }

    public function getError() {
        return $this->error;
    }

    public function getParams() {
        return $this->params;
    }

    public function redirect($request){
        $path = $_SERVER['SCRIPT_NAME'].'?module=slc';
        foreach($request as $key=>$val) {
            $path .= "&$key=$val";
        }

        header('HTTP/1.1 303 See Other');
        header("Location: $path");
        exit();
    }
}
