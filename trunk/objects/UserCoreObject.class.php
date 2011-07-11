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

class UserCoreObject extends UserBaseObject implements UserInterface {
	public function validate(){
		//Test username uniqueness
		if($this->get_id() == 0){
		
			if(!MagicUtils::is_unique('Users','username',$this->get_username())){
				$valid = false;
				$this->errors[] = new MagicValidationError("Username is not unique");
			}
		}
		if(count($this->errors) > 0){
			return FALSE;
		}else{
			return TRUE;
		}
	}
}