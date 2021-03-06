<?php
/*	Project:	EQdkp-Plus
 *	Package:	EQdkp-plus
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2016 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

class xmpp_notification extends generic_notification {
	
	private $objJabber = null;
	
	public function sendNotification($arrNotificationData){
		if(!$this->isAvailable()) return false;
		
		$intUserID = $arrNotificationData['to_userid'];
		$arrNotificationSettings = $this->pdh->get('user', 'notification_settings', array($intUserID));
		$jabberAccount = $arrNotificationSettings['ntfy_xmpp_user'];
		if($jabberAccount == "") return false;

		$strSubject = $this->user->lang('new_notification', false, false, $this->pdh->get('user', 'lang', array($arrNotificationData['to_userid']))).': '.$arrNotificationData['type_lang'];
		$strMessage = $strSubject."\n".$arrNotificationData['name']."\n".$arrNotificationData['link'];
		
		$this->messenger->sendMessage('xmpp', $intUserID, $strSubject, $strMessage);		
	}
	
	public function getAdminSettings(){
		return $this->messenger->getMethodAdminSettings('xmpp');
	}
	
	/* 
	 * @see generic_notification::getUserSettings()
	 */
	public function getUserSettings(){
		return $this->messenger->getMethodUserSettings('xmpp');
	}
	
	/* 
	 * @see generic_notification::isAvailable()
	 */
	public function isAvailable(){
		return $this->messenger->isAvailable('xmpp');
	}
}