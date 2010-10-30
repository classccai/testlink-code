<?php
/**
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Allows import in XML format of test plan links to:
 * Test Cases
 * Platforms
 *
 * works only if linked items ALREADY exist on system.
 *
 * @package 	TestLink
 * @author 		Francisco Mancardi
 * @copyright 	2003-2010, TestLink community 
 * @version    	CVS: $Id: planImport.php,v 1.3 2010/10/30 15:21:33 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal Revisions:
 * 20101017 - franciscom - BUGID 3649: Export/Import Test Plan links to test cases and platforms
 *
 **/
require('../../config.inc.php');
require_once('common.php');
require_once('xml.inc.php');

testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tplan_mgr = new testplan($db);
$args = init_args();
$gui = initializeGui($args,$tplan_mgr);
$dest_common = TL_TEMP_PATH . session_id(). "-planImport" ;
$dest_files = array('XML' => $dest_common . ".xml");
$input_file = $dest_files['XML'];

if(!is_null($args->importType))
{
	$input_file = $dest_files[$args->importType];
}

$gui->file_check = array('status_ok' => 1, 'msg' => 'ok');
$gui->import_title = lang_get('title_import_testplan_links');

// This check is done againg, also on importTestPlanLinksFromXML(), just to avoid surprises
$tproject_mgr = new testproject($db);
$dummy = $tproject_mgr->get_by_id($args->tproject_id);
$tprojectHasTC = $tproject_mgr->count_testcases($args->tproject_id) > 0; 
if(!$tprojectHasTC)
{
	$gui->resultMap[] = array('',sprintf(lang_get('tproject_has_zero_testcases'),$dummy['name']));
}


if ($args->do_upload)
{
  
	// check the uploaded file
	$source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;
	
	$doIt = false;
	$gui->file_check = null;
	if (($source != 'none') && ($source != ''))
	{ 
		// ATTENTION:
		// MAX_FILE_SIZE hidden input is defined on form, but anyway we do not get error at least using
		// Firefox and Chrome.
		if( !($doIt = $_FILES['uploadedFile']['size'] <= $gui->importLimitBytes) )
		{
			$gui->file_check['status_ok'] = 0;
			$gui->file_check['msg'] = sprintf(lang_get('file_size_exceeded'),$_FILES['uploadedFile']['size'],$gui->importLimitBytes);
		}
	}
	if($doIt)
	{ 
		$gui->file_check['status_ok'] = 1;
		if (move_uploaded_file($source, $input_file))
		{
			switch($args->importType)
			{
				case 'XML':
					$pimport_fn = "importTestPlanLinksFromXML";
				break;
			}
		}
		if($gui->file_check['status_ok'] && $pimport_fn)
		{
			$context = new stdClass();
			$context->tproject_id = $args->tproject_id;
			$context->tplan_id = $args->tplan_id;
			$context->userID = $args->userID;
			$gui->resultMap = $pimport_fn($db,$tplan_mgr,$input_file,$context);
		}
	}
	else if(is_null($gui->file_check))
	{
		$gui->file_check = array('status_ok' => 0, 'msg' => lang_get('please_choose_file_to_import'));
		$args->importType = null;
	}
}

$gui->testprojectName = $_SESSION['testprojectName'];
$gui->importTypes = $tplan_mgr->get_import_file_types();

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);  
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * called magically by TL to check if user trying to use this feature
 * has enough rights.
 *
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_testplan_create');
}

/**
 * process input data, creating a kind of namespace
 *
 * @global array _REQUEST
 *
 * @internal Revisions
 * 20101017 - franciscom - creation
 */
function init_args()
{
    $args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $args->importType = isset($_REQUEST['importType']) ? $_REQUEST['importType'] : null;
    $args->location = isset($_REQUEST['location']) ? $_REQUEST['location'] : null; 
    $args->do_upload = isset($_REQUEST['uploadFile']) ? 1 : 0;
    
    $args->userID = $_SESSION['userID'];
    $args->tproject_id = $_SESSION['testprojectID'];
    $args->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;
    
    return $args;
}


/**
 *
 *
 * 
 *
 * @internal Revisions
 * 20101017 - franciscom - creation
 */
function initializeGui(&$argsObj,&$tplanMgr)
{
	$guiObj = new stdClass();
	$guiObj->importLimitBytes = config_get('import_file_max_size_bytes');
	$guiObj->importLimitKB = ($guiObj->importLimitBytes / 1024);
	$guiObj->resultMap = null;
	
	$info = $tplanMgr->get_by_id($argsObj->tplan_id);
	$guiObj->main_descr = lang_get('testplan') . ' ' . $info['name'];
	$guiObj->tplan_id = $argsObj->tplan_id;
	$guiObj->import_done = false;
	return $guiObj;
}


/**
 *
 *
 * 
 *
 * @internal Revisions
 * 20101017 - franciscom - creation
 */
function importTestPlanLinksFromXML(&$dbHandler,&$tplanMgr,$targetFile,$contextObj)
{
	//   <testplan>
	//     <name></name>
	//     <platforms>
	//       <platform>
	//         <name> </name>
	//         <internal_id></internal_id>
	//       </platform>
	//       <platform>
	//       ...
	//       </platform>
	//     </platforms>
	//     <executables>
	//       <link>
	//         <platform>
	//           <name> </name>
	//         </platform>
	//         <testcase>
	//           <name> </name>
	//           <externalid> </externalid>
	//           <version> </version>
	//           <execution_order> </execution_order>
	//         </testcase>
	//       </link>
	//       <link>
	//       ...
	//       </link>
	//     </executables>
	//   </testplan>	 
	// </xml>
	

	// Double Check
	// Check if Test Plan Parent (Test Project) has testcases, if not abort
	$tprojectMgr = new testproject($dbHandler);
	$tprojectInfo = $tprojectMgr->get_by_id($contextObj->tproject_id);
	$tprojectHasTC = $tprojectMgr->count_testcases($contextObj->tproject_id) > 0; 
	if(!$tprojectHasTC)
	{
		$msg[] = array('',sprintf(lang_get('tproject_has_zero_testcases'),$tprojectInfo['name']));
		return $msg;  // >>>-----> Bye
	}
	
	$xml = @simplexml_load_file($targetFile);
	if($xml !== FALSE)
    {

		$tcaseMgr = new testcase($dbHandler);
		$tcXML['elements'] = array('string' => array("name"),  
								   'integer' => array("node_order","externalid"));
		$tcaseSet = array(); 
		$tprojectMgr->get_all_testcases_id($contextObj->tproject_id,$tcaseSet,array('output' => 'external_id'));
		$tcaseSet = array_flip($tcaseSet);
        // $linkedSet = $tplanMgr->get_linked_tcversions($contextObj->tplan_id);

    	// Test Plan name will not be used
    	// <testplan>  <name></name>
    	//
		// Platform definition info will not be used 
		//
		if( $xml->xpath('//executables') )
		{
			$tables = tlObjectWithDB::getDBTables(array('testplan_tcversions'));
			
			$labels = init_labels(array('link_without_required_platform' => null,
										'link_without_platform_element' => null,
										'link_with_platform_not_needed' => null,
										'platform_not_linked' => null, 'tcase_doesnot_exist' => null,
										'tcversion_doesnot_exist' => null,
										'link_2_tplan_feedback' => null, 'link_2_platform' => null ));
										
			$platformSet = $tplanMgr->getPlatforms($contextObj->tplan_id,array('outputFormat' => 'mapAccessByName'));
			$hasPlatforms = (count($platformSet) > 0);
			
			$xmlLinks = $xml->executables->children();
			// echo '<pre><xmp>';
			// var_dump($xmlLinks);			
			// echo '</xmp></pre>';
			
			$loops2do = count($xmlLinks);
			$msg = array();
			for($idx = 0; $idx < $loops2do; $idx++)
			{
				// if Target Test Plan has platforms and importing file NO => Fatal Error
				// echo '<pre><xmp>';
				$targetName = null;
				$platformID = -1;
				$linkWithPlatform = false;
				$status_ok = false;
				$dummy_msg = null;

				// $useCase = $hasPlatforms ? 'hasPlatforms' : 'none';
				if( ($platformElementExists = property_exists($xmlLinks[$idx],'platform')) )
				{
					$targetName = trim((string)$xmlLinks[$idx]->platform->name);
					$linkWithPlatform = ($targetName != '');
				}

				// echo "\$hasPlatforms:$hasPlatforms<br>";
				// echo "\$linkWithPlatform:$linkWithPlatform<br>";
				if($hasPlatforms)
				{
					// each link need to have platform or will not be imported
				    // if( $platformElementExists && $linkWithPlatform && isset($platformSet[$targetName]))
				    if( $linkWithPlatform && isset($platformSet[$targetName]))
				    {
						$platformID = $platformSet[$targetName]['id'];
						$status_ok = true;
						$dummy_msg = null;
				    }
				    else
				    {
				    	if( !$platformElementExists )
				    	{
							$dummy_msg = sprintf($labels['link_without_platform_element'],$idx+1);				
				    	}
				    	else if(!$linkWithPlatform)
				    	{
							$dummy_msg = sprintf($labels['link_without_required_platform'],$idx+1);				
				    	}
				    	else
				    	{
				    		$dummy_msg = sprintf($labels['platform_not_linked'],$idx+1,$targetName,$contextObj->tplan_name);
				    	}
				    } 
				    
				}
				else
				{
					if( $linkWithPlatform )
					{
						$dummy_msg = sprintf($labels['link_with_platform_not_needed'],$idx+1);				
					}
					else
					{
						$platformID = 0;
						$status_ok = true;	
					}
				}				
				if( !is_null($dummy_msg) )
				{
					$msg[] = array($dummy_msg);
				}
				
				if( $status_ok )
				{
				
					// Link passed ok check on password
					// Now we need to understand if requested Test case is present on Test Project
        			// $dummy = getItemsFromSimpleXMLObj(array($xmlTCs[$idx]),$tcXML);
        			// $tc = $dummy[0]; 
					$externalID = (int)$xmlLinks[$idx]->testcase->externalid;
					$tcaseName = (string)$xmlLinks[$idx]->testcase->name;
					$execOrder = (int)$xmlLinks[$idx]->testcase->execution_order;
					$version = (int)$xmlLinks[$idx]->testcase->version;

					if( isset( $tcaseSet[$externalID] ) )
					{
						// now need to check if requested version exists
						$dummy = $tcaseMgr->get_basic_info($tcaseSet[$externalID],$version);
						if( count($dummy) > 0 )
						{
							// Now need to understand if is already linked
							$sql = " SELECT * FROM {$tables['testplan_tcversions']} " .
								   " WHERE testplan_id = {$contextObj->tplan_id} " .
								   " AND tcversion_id = {$dummy[0]['tcversion_id']} " .
								   " AND platform_id = {$platformID} ";
								   
							$isLinked = $dbHandler->get_recordset($sql);	   
							if( count($isLinked) <= 0 )
							{
								// Create link
								// function link_tcversions($id,&$items_to_link,$userId)
								$item2link['items'] = array($dummy[0]['id'] => array($platformID => $dummy[0]['tcversion_id']));
								$item2link['tcversion'] = array($dummy[0]['id'] => $dummy[0]['tcversion_id']);
								$tplanMgr->link_tcversions($contextObj->tplan_id,$item2link,$contextObj->userID);
								$dummy_msg = sprintf($labels['link_2_tplan_feedback'], $externalID, $version);
								
								if( $platformID > 0 )
								{
									$dummy_msg .= spritnf($labels['link_2_platform'],$targetName);
								}
								$msg[] = array($dummy_msg);
							}							
						}
						else
						{
							$msg[] = array(sprintf($labels['tcversion_doesnot_exist'],$externalID,$version,$tprojectInfo['name']));
						}
					}
					else
					{
						$msg[] = array(sprintf($labels['tcase_doesnot_exist'],$externalID,$tprojectInfo['name']));
					}
					//$tcaseMgr->get_by_external
							
					// echo '<pre><xmp>';
					// var_dump($xmlLinks[$idx]->testcase);
					// echo 'TCBAME' . (string)$xmlLinks[$idx]->testcase->name;			
					// echo '</xmp></pre>';
				}
			
			}	

		
		
		}
			
	}
	return $msg;
}
?>