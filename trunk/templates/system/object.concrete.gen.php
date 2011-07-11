<?php echo "<?php\n"; ?>
<?php include(ROOT . "/templates/system/txt.licence.php"); ?>

/**
 * Concrete Class generated <?=date("F j, Y, g:i:s a"); ?>.
 */

final class <?=$this->name?> extends <?=$this->name?>Object implements <?=$this->name?>Interface {
   /**
     * Factory function. Returns an instance of <?=$this->name?>.
     * @return <?=$this->name?> .
     */
	static public function Factory(){
		return new <?=$this->name?>();
	}

    /**
     * Cast function. Casts $object to an instance of <?=$this->name?>.
     * Mostly used to allow type hinting.
     * @return <?=$this->name?> .
     */
    static public function Cast( <?=$this->name?> &$object){
        return $object;
    }

<?php
	/*
	 * Loop over the variables again, make the getters and setters
	 */
   if(USE_CONCRETE_GETSET_WRAPPERS === TRUE){
	foreach($this->definition as $variable_name => $definition){
?>
   public function get_<?=$variable_name?>() {
      return parent::get_<?=$variable_name?>();
   }
   public function get_db_safe_<?=$variable_name?>() {
      return parent::get_db_safe_<?=$variable_name?>();
   }
   public function set_<?=$variable_name?>($<?=$variable_name?>){
      return parent::set_<?=$variable_name?>($<?=$variable_name?>);
   }

<?  } ?>
<? } ?>

   public function save($validate = TRUE){
       return parent::save($validate);
   }

}