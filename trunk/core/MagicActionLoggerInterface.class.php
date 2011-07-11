<?php

interface MagicActionLoggerInterface {

   /**
    * @static
    * @abstract
    * @return MagicActionLogger
    */
   static public function Factory();

   public function set_variable($variable);
   public function set_before($before);
   public function set_after($after);

}