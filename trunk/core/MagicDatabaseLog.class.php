<?php 


class MagicDatabaseLog {
   public $time_begin;
   public $time_end;
   public $query;
   public $time_to_execute;

   public function calculate_runtime(){
       $this->time_to_execute = $this->time_end - $this->time_begin;
       return $this->time_to_execute;
   }
   public function get_row(){
      $this->calculate_runtime();
      $query = trim($this->query);
      $query = str_replace("\n", " ", $query);
      $query = str_replace("\t", "", $query);
     return round($this->time_to_execute, 4) ." sec : {$query}";
   }
}

