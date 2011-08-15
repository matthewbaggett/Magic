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


class SettingCoreController extends SettingBaseController {
	/**
	 * Lazy function to get a setting's value by its system name.
	 * @param string $name system name of the setting you want to fetch
	 * @return string value of the setting you want to fetch
	 */
	static public function get($name){
		$setting = SettingSearcher::Factory()->search_by_system_name($name)->execute_one();
		if($setting != null){
			return $setting->get_value();
		}else{
			return null;
		}
	}
}
