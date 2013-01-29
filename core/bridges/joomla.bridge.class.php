<?php
 /*
 * Project:		eqdkpPLUS Libraries: myHTML
 * License:		Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:		2008
 * Date:		$Date$
 * -----------------------------------------------------------------------
 * @author		$Author$
 * @copyright	2006-2011 EQdkp-Plus Developer Team
 * @link		http://eqdkp-plus.com
 * @package		libraries:myHTML
 * @version		$Rev$
 * 
 * $Id$
 */

if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

class joomla_bridge extends bridge_generic {
	
	public $name = "Joomla";
	
	public $data = array(
		//Data
		'groups' => array( //Where I find the Usergroup
			'table'	=> 'usergroups', //without prefix
			'id'	=> 'id',
			'name'	=> 'title',
			'QUERY'	=> '',
		),
		'user_group' => array( //Zuordnung User zu Gruppe
			'table'	=> 'user_usergroup_map',
			'group'	=> 'group_id',
			'user'	=> 'user_id',
			'QUERY'	=> '',
		),
		'user'	=> array( //User
			'table'	=> 'users',
			'id'	=> 'id',
			'name'	=> 'username',
			'where'	=> 'username',
			'password' => 'password',
			'email'	=> 'email',
			'salt'	=> '',
			'QUERY'	=> '',
		),
	);
	
	public $functions = array(
		'login'	=> array(
			'callbefore'	=> '',
			'function' 		=> '',
			'callafter'		=> 'joomla_callafter',
		),
		'logout' 	=> '',
		'autologin' => '',	
		'sync'		=> '',
	);
	
	//Needed function
	public function check_password($password, $hash, $strSalt = '', $boolUseHash){
		list($strHash, $strSalt) = explode(':', $hash);
		if (md5($password.$strSalt) == $strHash){
			return true;
		}
		return false;
	}
	
	public function joomla_callafter($strUsername, $strPassword, $boolAutoLogin, $arrUserdata, $boolLoginResult, $boolUseHash){
		//Is user active?
		if ($boolLoginResult){
			if ($arrUserdata['block'] != '0') {
				return false;
			}
		}
		return true;
	}
}
?>