<?php
namespace slc;
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

        if ( ! file_exists( '\\mod\\slc\\class\\views\\'.$file ) ) {
            throw new \slc\exceptions\ViewNotFoundException( "Could not find view '$file'" );
        }

        if ( ! class_exists( "\\slc\\views\\".$class ) ) {
            throw new \slc\exceptions\ViewNotFoundException( "No view class '$class' located" );
        }
       
        $class = "\\slc\\views\\".$class;
        $view = new $class();
        
        return $view;
    }
}

?>
