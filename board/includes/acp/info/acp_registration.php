<?php
/**
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group
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

class acp_registration_info
{
    function module()
    {
        return array(
            'filename'    => 'acp_registration',
            'title'        => 'Registration',
            'version'    => '1.0.0',
            'modes'        => array(
            	// Please work
                'overview'      => array('title' => 'Overview', 'auth' => 'acl_a_schools', 'cat' => array('ssuns')),
                'assign'		=> array('title' => 'Assignments', 'auth' => 'acl_a_schools', 'cat' => array('ssuns')),
                'finances'		=> array('title' => 'Financial information', 'auth' => 'acl_a_schools', 'cat' => array('ssuns')),
                'matrix'		=> array('title' => 'Country-committee matrix', 'auth' => 'acl_a_schools', 'cat' => array('ssuns')),
                'characters'	=> array('title' => 'Character lists', 'auth' => 'acl_a_schools', 'cat' => array('ssuns')),
                'delegates'		=> array('title' => 'Delegate overview', 'auth' => 'acl_a_schools', 'cat' => array('ssuns')),
                'papers'		=> array('title' => 'Position papers', 'auth' => 'acl_a_papers', 'cat' => array('ssuns')),
                'events'		=> array('title' => 'Event registration', 'auth' => 'acl_a_schools', 'cat' => array('ssuns')),
                'final'			=> array('title' => 'Final position papers', 'auth' => 'acl_a_papers', 'cat' => array('ssuns')),
            ),
        );
    }

    function install()
    {
    }

    function uninstall()
    {
    	// ONCE YOU INSTALL YOU CAN NEVER GO BACK MUAHHAHA
    }
}

?>
