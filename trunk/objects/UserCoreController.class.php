<?php
/*    +-----------------------------------------------------------------------+
 *    | Socializr & Magic Framework                                           |
 *    +-----------------------------------------------------------------------+
 *    | Copyright (c) 2009-2011 The Magic Group                               |
 *    +-----------------------------------------------------------------------+
 *    | This source file is the property of The Magic Group (hereby known as  |
 *    | "Us", or "we". We're a nice bunch :) We're an approachable  lot       |
 *    | for licencing~                                                        |
 *    |                                                                       |
 *    | You can contact us with one of the emails below:                      |
 *    +-----------------------------------------------------------------------+
 *    | Authors: Matthew Baggett <matthew@baggett.me>                         |
 *    |          Magic Generator <hello@turbocrms.com>                        |
 *    +-----------------------------------------------------------------------+
 */
 
// $Id:$


class UserCoreController extends UserBaseController {

	public function loginAction(){
		
	}
	public function registerAction(){
		
	}	

   static public function login($username, $password){
      $results = UserSearcher::Factory()
         ->search_by_username($username)
         ->search_by_password(self::hash_password($password))
	  	 ->search_by_active(User::ACTIVE_ACTIVE)
         ->execute();
      if(count($results) == 0){
         //throw new MagicLoginException("Cannot login, password or username does not match");
         $oUser->loginFailure();
         return NULL;
      }elseif(count($results) == 1){
      	 $oUser = end($results);
      	 $oUser = User::Cast($oUser);
      	 $oUser->loginSuccessful();
      	 $_SESSION['user'] = $oUser;
         return $_SESSION['user']; 
      }else{
         throw new MagicLoginException("Something crazy happened. There are two (or more) matching users for the details supplied");
      }
   }
   
   static public function hash_password($password){
      return hash("SHA1",$password);
   }
}
