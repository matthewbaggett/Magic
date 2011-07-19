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
      $data = self::backup();
      $yql = array();
      foreach ($data as $row) {
         $yql_row = get_called_class().":\n";
         foreach ($row as $column => $value) {
            $yql_row .= "  {$column}: {$value}\n";
         }
         $yql[] = $yql_row;
      }
      return trim(implode("\n", $yql));
   }
}
