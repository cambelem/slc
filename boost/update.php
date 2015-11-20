<?php
namespace slc;
/**
 * Updates the module
 *
 * @author Chris Coley <chris at tux dot appstate dot edu>
 */

function slc_update(&$content, $current_version) {
    switch(1) {
        case version_compare($current_version, '2.0.3', '<'):
            $content[] = '<pre>';
            
            $db = new \PHPWS_DB('slc_problem');
            $db->addWhere('id', 16);
            $db->addValue('description', 'Tenancy / Eviction');

            if (\PHPWS_Error::logIfError($db->update())) {
                $content[] = 'Unable to change "Tenancy/Eviction" to "Tenancy / Eviction" in table slc_problem.';
                return false;
            } else {
                $content[] = 'Changed "Tenancy/Eviction" to "Tenancy / Eviction" in table slc_problem';
            }

            slcUpdateFiles(array(   'class/ajax/GETReport.php',
                                    'class/views/ViewReports.php',
                                    'javascript/report/head.js',
                                    'templates/Report.tpl',
                                    'boost/install.sql',
                                    'boost/boost.php',
                                    'boost/update.php'), $content);

            $content[] = '2.0.3 changes
---------------
+ Added date selectors to all reports to limit data to a specific timespan.
+ Fixed the Landlord-Tenant report display issue in Firefox.</pre>';
        
        case version_compare($current_version, '2.0.4', '<'):
            $content[] = '<pre>';
            
            $db = new \PHPWS_DB('slc_landlord');
            $db->addWhere('id', 94);
            $db->addValue('name', 'Other / Unspecified');
            if (\PHPWS_Error::logIfError($db->update())) {
                $content[] = 'Unable to change "Other / Not Provided" to "Other / Unspecified" in table slc_landlord.';
                return false;
            }

            $db->reset();
            $db->addWhere('id', 70);
            $db->addValue('name', 'Roger Pope');
            if (\PHPWS_Error::logIfError($db->update())) {
                $content[] = 'Unable to move "Roger Pope" from id=95 to id=70';
                return false;
            }

            $db->reset();
            $db->addWhere('id', 95);
            if (\PHPWS_Error::logIfError($db->delete())) {
                $content[] = 'Unable to delete Roger Pope\'s old id (95) from slc_landlord.';
                return false;
            }

            $content[] = 'Successfully merged "Unknown" and "Other / Not Provided" into "Other / Unspecified" in table slc_landlord.';

            $db = new \PHPWS_DB('slc_issue');
            $db->addWhere('landlord_id', 70);
            $db->addValue('landlord_id', 94);
            if (\PHPWS_Error::logIfError($db->update())) {
                $content[] = 'Unable to move issues assigned to landlord "Unknown" to landlord "Other / Unspecified".';
                return false;
            } else {
                $content[] = 'Successfully moved issues assigned to landlord "Unknown" to landlord "Other / Unspecified".';
            }

            $db->reset();
            $db->addWhere('landlord_id', 95);
            $db->addValue('landlord_id', 70);
            if (\PHPWS_Error::logIfError($db->update())) {
                $content[] = 'Unable to move issues assigned to landlord "Roger Pope" from his old id (95) to his new id (70).';
                return false;
            } else {
                $content[] = 'Successfully moved issues assigned to landlord Roger Pope\'s old id (95) to his new id (70).';
            }

            slcUpdateFiles(array(   'boost/install.sql',
                                    'boost/update.php',
                                    'boost/boost.php',
                                    'class/ajax/GETReport.php',
                                    'class/ajax/POSTNewIssue.php'), $content);
            
            $content[] = '2.0.4 changes
---------------
+ Merged "Unknown" and "Other / Not Provided" landlords into "Other / Unspecified".
+ Updated the reports and issue creation to reflect this merging.<pre>';
    
        case version_compare($current_version, '2.0.5', '<'):
            $content[] = '<pre>';
            slcUpdateFiles(array(   'boost/boost.php',
                                    'boost/update.php',
                                    'class/ajax/GETReport.php',
                                    'class/views/ViewReports.php',
                                    'javascript/report/head.js',
                                    'templates/Report.tpl',
                                    'class/ajax/ExportCSV.php'), $content);
            $content[] = '2.0.5 changes
---------------
+ Fixed some math issues in "Landlord/Tenant" and "Condition by Landlord" reports.
+ Added CSV export for the "Landlord/Tenant", "Condition by Landlord", and "Appointment Statistics" reports.
<pre>';
        
        case version_compare($current_version, '2.0.6', '<'):
           $db = new \PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR .
                            'mod/slc/boost/updates/update_2_0_6.sql');
            if(\PHPWS_Error::logIfError($result)){
                return $result;
             }

        case version_compare($current_version, '2.0.7', '<'):
         	$db = new \PHPWS_DB();
         	$result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/slc/boost/updates/update_2_0_7.sql');
         	if (\PEAR::isError($result)) {
 	        	return $result;
         	}  
        case version_compare($current_version, '3.0.0', '<'):
            $db = new \PHPWS_DB();
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/slc/boost/updates/update_3_0_0.sql');
            if (\PEAR::isError($result)) {
                return $result;
            }     
    }
    return true;
}

function slcUpdateFiles($files, &$content) {
    if (\PHPWS_Boost::updateFiles($files, 'checkin')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "    " . implode("\n    ", $files);
}

?>
