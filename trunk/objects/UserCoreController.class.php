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

	public function logoutAction(){
		unset($_SESSION['user']);
		session_destroy();
		MagicUtils::redirect("Users","Login");
	}
	public function loginAction(){
		if(count($_POST) > 0){
			$oUser = self::login($_POST['username'],$_POST['password']);
			if($oUser !== NULL){
				$this->loginSuccessful();
			}else{
				$this->loginFailure();
			}
		}
	}
	public function loginSuccessful(){
		MagicUtils::redirect("Favourites");
	}
	public function loginFailure(){
		$this->application->page->failure_message = "Failed to log in. Either the User does not exist, or the password was bad. Or the Account could have been disabled by an administrator.";
	}
	public function registerAction(){
		
	}	

   static public function login($username, $password){
   	
   	  $login_query_username = UserSearcher::Factory()
         ->search_by_username($username)
         ->search_by_password(self::hash_password($password))
	  	 ->search_by_active(User::ACTIVE_ACTIVE)
	  	 ->execute();
   	  $login_query_email = UserSearcher::Factory()
         ->search_by_email($username)
         ->search_by_password(self::hash_password($password))
	  	 ->search_by_active(User::ACTIVE_ACTIVE)
	  	 ->execute();
   	  $results = array_merge((array) $login_query_username, (array) $login_query_email);   
      if(count($results) == 0){
         //throw new MagicLoginException("Cannot login, password or username does not match");
         return NULL;
      }elseif(count($results) == 1){
      	 $oUser = end($results);
      	 $oUser = User::Cast($oUser);
      	 $_SESSION['user'] = $oUser;
         return $_SESSION['user']; 
      }else{
         throw new MagicLoginException("Something crazy happened. There are two (or more) matching users for the details supplied");
      }
   }
   
   static public function hash_password($password){
      return hash("SHA1",$password);
   }
   
   static public function is_logged_in(){
   		if(isset($_SESSION['user'])){
			if($_SESSION['user'] instanceof User){
				if($_SESSION['user']->get_id() > 0){
					if($_SESSION['user']->get_active() == 'active'){
						//All is well
						return TRUE;
					}
				}		
			}
		}
		return FALSE;
   }
}
