<?php
/**
*
* @package Custom Pages MOD
* @version $Id: acp_custom_pages.php ilostwaldo@gmail.com$
* @copyright (c) 2011 dellsystem (www.dellsystem.me)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_custom_pages {
   var $u_action;
   var $new_config;
   
   function main($id, $mode)
   {
		global $phpbb_root_path, $db, $phpEx, $auth, $user, $template, $config;
		
		$user->add_lang('mods/custom-pages/acp');
     	$submit = (isset($_POST['submit'])) ? true : false;
		switch($mode)
		{
			case 'overview':
				$this->page_title = 'CUSTOM_PAGES_OVERVIEW';
				$this->tpl_name = 'acp_custom_pages_overview';
            
		        // Do a SQL query to fetch the 5 most recently added custom pages (just the titles etc, no content)
		        $sql = "SELECT page_id, page_title, page_name, last_modified, page_template
		        		FROM " . CUSTOM_PAGES_TABLE . "
		        		ORDER BY last_modified DESC
		        		";
//		        		LIMIT 5";
		       	$result = $db->sql_query($sql);
           	
				while ( $row = $db->sql_fetchrow($result) ) {
					$template->assign_block_vars('pages', array(
						'PAGE_ID' 		=> $row['page_id'],
						'PAGE_TITLE' 	=> $row['page_title'],
						'TEMPLATE_FILE' => $row['page_template'],
						'PAGE_NAME'		=> $row['page_name'],
						'LAST_MODIFIED'	=> $user->format_date($row['last_modified']),
						'U_EDIT'		=> $this->u_action . '&amp;mode=edit&amp;id=' . $row['page_id'],
						'U_DELETE'		=> $this->u_action . '&amp;mode=delete&amp;id=' . $row['page_id'],
						'U_PAGE'		=> $phpbb_root_path . '../' . $row['page_name'])
					);
				}
            break;
         case 'add':
         	$this->page_title = 'Add a new custom page';
         	$this->tpl_name = 'acp_custom_pages_add';
         	
         	// Only really need to do stuff if we're submitting
         	if ( $submit ) {
         		// First check to make sure that none of the elements are empty
         		$new_title = utf8_normalize_nfc(request_var('page_title', ''), true);
         		$new_name = request_var('page_name', '');
         		$new_content = utf8_normalize_nfc(request_var('page_content', '', true));
         		// Eventually this should be a dropdown menu - all the template files in the dir? prefixed with cp_
         		$new_template = request_var('page_template', '');
         		
         		$sql_array = array(
         			'page_title'	=> $new_title,
         			'page_name'		=> $new_name,
         			'page_content'	=> $new_content,
         			'page_template'	=> $new_template,
         			'last_modified'	=> time()
         		);
         		
         		if ( $new_title == '' || $new_name == '' || $new_content == '' ) {
         			trigger_error('None of the first three fields can be empty!' . adm_back_link($this->u_action), E_USER_WARNING);
         		}
         	
         		
         		if ( !($this->unique_name($new_name)) ) {
         			trigger_error('Your page name (URL) is not unique!' . adm_back_link($this->u_action), E_USER_WARNING);
         		}

         		// Otherwise, might as well add the page ... ignore page_id, that autoincrements
         		$sql = "INSERT INTO " . CUSTOM_PAGES_TABLE . "
         				" . $db->sql_build_array('INSERT', $sql_array);
         		$db->sql_query($sql);
         		
         		add_log('Added custom page /' . $new_name . adm_back_link($this->u_action));
         		trigger_error('Your page has been successfully added' . adm_back_link($this->u_action));
         	}
         	break;
         case 'edit':
         	$this->page_title = 'Edit a custom page';
         	// Why doesn't add use the same template file?
         	$this->tpl_name = 'acp_custom_pages_edit';
         	
         	// Store form variables first
         	$id_to_edit = request_var('id', 0);
         	$new_title = utf8_normalize_nfc(request_var('page_title', '', true));
         	$new_name = request_var('page_name', '');
         	$new_content = utf8_normalize_nfc(request_var('page_content', '', true));
         	$new_template = request_var('page_template', '');
        	
         	// First try to query the database looking for this page
         	$sql = "SELECT *
         			FROM " . CUSTOM_PAGES_TABLE . "
         			WHERE page_id = $id_to_edit
         			LIMIT 1";
         	$result = $db->sql_query($sql);
         	$row = $db->sql_fetchrow($result);
         	
         	$page_nonexistent = ( intval($row['page_id']) == 0 ) ? true : false;
         	
         	// If we're submitting and the page exists (it should ...)
         	if ( $submit && !$page_nonexistent )
         	{
         	
		     	// Use sql_build_array() to build the update statement
		     	// Code reuse ... move this somewhere else later?
		     	$sql_array = array(
		     		'page_title'	=> $new_title,
		     		'page_name'		=> $new_name,
		     		'page_content'	=> $new_content,
		     		'page_template'	=> $new_template,
		     		'last_modified'	=> time()
		     	);
		     	
		     	if ( $new_title == '' || $new_name == '' || $new_content == '' ) {
		     		trigger_error('None of the first three fields can be empty!' . adm_back_link($this->u_action . '&amp;id=' . $id_to_edit), E_USER_WARNING);
		     	}
		     	
		     	
				// Update the table:
				$sql = "UPDATE " . CUSTOM_PAGES_TABLE . "	
						SET " . $db->sql_build_array('UPDATE', $sql_array) . "
						WHERE page_id = $id_to_edit";
				$db->sql_query($sql);         	
         	
         		add_log('admin', 'Edited custom page /' . $row['page_name']);

				trigger_error('Successfully updated custom page' . adm_back_link($this->u_action . "&amp;id=$id_to_edit"));
         	}
         	
         	$template->assign_vars(array(
         		'PAGE_ID'			=> $row['page_id'],
         		'CUSTOM_PAGE_TITLE'	=> $row['page_title'], // otherwise it conflicts lol
         		'PAGE_NAME'			=> $row['page_name'],
         		'PAGE_CONTENT'		=> $row['page_content'],
         		'LAST_MODIFIED'		=> $user->format_date($row['last_modified']),
         		'PAGE_TEMPLATE'     => $row['page_template'],
         		'PAGE_NONEXISTENT'	=> $page_nonexistent,)
         	);
         	break;
         case 'delete':
         	$id_to_delete = request_var('id', 0);
         	
         	// Do the confirm box thing whatever before deleting
			if (confirm_box(true))
			{
				// If we need to delete a layer, the delete get var will be > 0 (will be the ID)
				$sql = "DELETE FROM " . DYNAMO_LAYERS_TABLE . "
						WHERE dynamo_layer_id = $delete_get";
				$db->sql_query($sql);
			
				// Now set the associated items to an item ID of 0 (uncategorised)
				$sql = "UPDATE " . DYNAMO_ITEMS_TABLE . "
						SET dynamo_item_layer = 0
						WHERE dynamo_item_layer = $delete_get";
				$db->sql_query($sql);
				 
				trigger_error($user->lang['ACP_DYNAMO_DELETED_LAYER'] . adm_back_link($this->u_action));
			}
			else
			{
				$s_hidden_fields = build_hidden_fields(array(
					'submit'    => true,
					)
				);
				
				confirm_box(false, $user->lang['ACP_DYNAMO_DELETE_LAYER'], $s_hidden_fields);
			}
         	break;
      	}
     }
		// Helper function for determining if the page_name is unique - returns true or false
		function unique_name($name_to_check) {
   			global $db;
   			$sql = "SELECT page_id
   					FROM " . CUSTOM_PAGES_TABLE . "
   					WHERE page_name = '$name_to_check'";
   			$result = $db->sql_query($sql);
   			$row = $db->sql_fetchrow($result);
   			if ( intval($row['page_id']) > 0 ) {
   				return false;
   			} else {
   				return true;
   			}
   			return false;
	}
}

?>
