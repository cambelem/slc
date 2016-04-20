<?php
/*
 * Some constants used throughout the application.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package mod
 * @subpackage slc
 */

define('STUDENT_FRESHMAN',  0);
define('STUDENT_SOPHOMORE', 1);
define('STUDENT_JUNIOR',    2);
define('STUDENT_SENIOR',    3);
define('STUDENT_GRADUATE',  4);
define('STUDENT_OTHER',     5);

function getYearList(){
    return array(STUDENT_FRESHMAN  => 'Freshman',
                 STUDENT_SOPHOMORE => 'Sophomore',
                 STUDENT_JUNIOR    => 'Junior',
                 STUDENT_SENIOR    => 'Senior',
                 STUDENT_GRADUATE  => 'Graduate',
                 STUDENT_OTHER     => 'Other');
}
 
