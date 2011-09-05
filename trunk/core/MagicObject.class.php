<?php 
class MagicObject extends MagicObjectCore implements MagicSavableInterface {

   protected $errors;

   public function __construct() {
	  $this->errors = array();
      parent::__construct();
   }


   public function save($force_save = false) {
      if (method_exists($this, 'generate_hash')) {
         $this->generate_hash();
      }
      $this->core_save($force_save);

   }
   public function reload(){
   	if($this->get_id() < 1){
   		throw new exception("Cannot reload unsaved object");
   	}
   	$this->load($this->get_id());
   	return $this;
   }

   public function load($id = null) {
      $this->core_load($id);
   }

   public function validate() {
      $errors = array();
      return $errors;
   }

   static public function backup() {
      $backup_query = new MagicQuery("SELECT", Inflect::pluralize(get_called_class()));
      return $backup_query->execute();
   }

   static public function backup_yql() {
      echo "Downloading...";
      $data = self::backup();
      $yql = array();
      $row_total = count($data);
      $i = 0;
      foreach ($data as $row) {
         $i++;
         echo "\rProcessing $i of $row_total";
         $yql_row = get_called_class().":\n";
         foreach ($row as $column => $value) {
            $yql_row .= "  {$column}: {$value}\n";
         }
         $yql[] = $yql_row;
      }
      echo "\r";
      return trim(implode("\n", $yql));
   }
   
   public function get_id36(){
      return base_convert($this->get_id(),10,36);
   }
   
   public function get_named_column($column){
      $function_to_call = "get_{$column}";
      if(method_exists($this,$function_to_call)){
      	return call_user_method($function_to_call, $this);
      }else{
      	throw new MagicException("No such method {$column} on this object!");
      }
   }
}
