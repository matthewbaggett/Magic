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


/**
 * Object Core generated August 4, 2011, 3:51:07 pm.
 * Core layer is provided so that objects can be influenced at the framework level.
 */

class PageCoreObject extends PageBaseObject implements PageInterface {

	public function get_path(){
		$path =  str_replace(" ","_",parent::get_path());
		$path = preg_replace("/[^a-zA-Z0-9\s]/", "_", $path);
		return $path;
	}
}