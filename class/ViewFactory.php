<?php
/*
 * View Factory
 *
 * Attempts to instantiate a given view.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package mod
 * @subpackage slc
 */

class ViewFactory {

    public static function getView($view = 'Main'){
        if ( preg_match( '/\W/', $view ) ) {
            throw new Exception("Illegal characters in view");
        }

        $class = "View".$view;//UCFirst(strtolower($view));

        $file = "{$class}.php";

        if ( !PHPWS_Core::initModClass( 'slc', 'views/'.$file ) ) {
            throw new ViewNotFoundException( "Could not find view '$file'" );
        }

        if ( ! class_exists( $class ) ) {
            throw new ViewNotFoundException( "No view class '$class' located" );
        }
       
        $view = new $class();
        
        return $view;
    }
}

?>
