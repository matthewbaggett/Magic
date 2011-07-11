<?php

class MagicPerformanceLog{

   static $instance;
   static $instance_instantiation;

   private $timeline;

   static public function get_instance(){
      if(!self::$instance instanceof MagicPerformanceLog){
         self::$instance_instantiation = microtime(true);
         self::$instance = new MagicPerformanceLog();
      }
      return self::$instance;
   }

   static public function mark($label = null){
      $mpl = MagicPerformanceLog::get_instance();
      $mpl->add_mark($label);
   }

   public function add_mark($label = null){
      $microtime = microtime(true);
      $oMark = new MagicPerformanceLogMark();
      $oMark->label = $label;
      $oMark->memory = memory_get_usage();
      $oMark->microtime = $microtime;
      $oMark->trace =  debug_backtrace(false);
      $oMark->called_from = $oMark->trace[2]['class'].$oMark->trace[2]['type'].$oMark->trace[2]['function'];

      $this->timeline[] = $oMark;
   }

   public function __construct(){
      $this->startup = microtime(true);
      $this->add_mark("Startup",$this->startup);
   }
   
   public function __destruct(){

   }

   public function render_log(){
      ksort($this->timeline);
      $startup_time = MagicPerformanceLog::$instance_instantiation;
      echo "<!--\n";
      echo "Startup time: $startup_time\n";
      $last_time = 0;
      echo "(time since start : time since last mark) - Label\n";
      foreach($this->timeline as $oLogMark){
         if($last_time!= 0){
            $time_since_last = number_format($oLogMark->microtime - $last_time,4);
         }else{
            $time_since_last = 0;
         }
         $time_since_start = number_format($oLogMark->microtime - $startup_time,4);

         echo "({$time_since_start} : {$time_since_last})";
         echo " - {$oLogMark->called_from}";
         if($oLogMark->label !== NULL){
            echo " - {$oLogMark->label}";
         }
         echo "\n";
         
         $last_time = $oLogMark->microtime;
      }
      echo "-->\n";
   }


}

MagicPerformanceLog::get_instance()->add_mark("Performance Log Start");