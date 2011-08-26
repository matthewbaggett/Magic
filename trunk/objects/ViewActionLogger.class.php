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


/*
 * Action Logger generated August 26, 2011, 5:17:05 pm
 * This class is to log the actions and events that occour on MagicObjects
 * 
 * 
 *                            The demanding Action Logger Generator Faerie
 */
/**
* Action Logger generated August 26, 2011, 5:17:05 pm.
*/

class ViewActionLogger extends MagicActionLogger implements MagicActionLoggerInterface, MagicObjectImplementation {

   protected $logged_element = 'View';
   protected $_table = 'ActionLog_Views';
   protected $key;
   protected $variable;
   protected $before;
   protected $after;

   static public function Factory(){
      return new ViewActionLogger();
   }

   public function set_view_id($id){
      $this->key = $id;
      return $this;
   }

   public function save($force_save = false){
        return false;
   }
}
