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

        $class = "\\slc\\views\\".$class;
        $view = new $class();

        return $view;
    }
}
