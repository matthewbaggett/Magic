<?php echo "<?php\n"; ?>
<?php include(ROOT . "/templates/system/txt.licence.php"); ?>

/**
 * Interface generated <?=date("F j, Y, g:i:s a"); ?>.
 */

interface <?=$this->name?>Interface{

   static public function backup();

   static public function backup_yql();
   <?php
	foreach($this->definition as $variable_name => $definition){
   ?>

   /**
    * @return <?=$definition['type']?> 
    */
   public function get_<?=$variable_name?>();
   /**
    * @return <?=$this->name?> 
    */
   public function set_<?=$variable_name?>($<?=$variable_name?>);
   <?php
   }
	foreach($this->definition as $variable_name => $definition){
		if($definition['type'] == 'hash'){
		?>
			//protected function generate_hash_<?=$variable_name?>();
		<?
		}
	}
   ?>

	function generate_hash();

	<?php
	/*
	 * Loop over the variables again, make the validators
	 */
	foreach($this->definition as $variable_name => $definition){
	?>
   //private function validate_<?=$variable_name;?>();
	<?php } ?>

    /**
     * Run the validation necessary for this object
     * @return Boolean
     */
	public function validate();

    /**
     * Save this object to the persistance layer or database
     * @attribute $validate Boolean Wether or not to run the validator by default
     * @return <?=$this->name?>
     */
	public function save($validate = TRUE);
<?
    foreach($this->definition as $variable_name => $definition){
        if(isset($definition['foreign'])){
            $bits = explode(".",$definition['foreign']);
            $foreign_class = $bits[0];
            $foreign_variable = $bits[1];
?>
	public function get_parent_<?=strtolower($foreign_class)?>();
<?php
        }
    }

    foreach($this->objectmap as $object_name => $object){
        //echo "// $object_name\n";
        foreach($object as $variable_name => $variable){
            //echo "//   $variable_name\n";
            if(isset($variable['foreign'])){

                $bits = explode(".",$variable['foreign']);
                $foreign_class = $bits[0];
                $foreign_variable = $bits[1];
                //echo "//     Has a foreign: {$foreign_class}.{$foreign_variable}\n";
                //echo "//       My class: " . $this->name. "\n";
                if($foreign_class == $this->name){
                    $object_name_for_getter = Inflect::pluralize(MagicUtils::from_camel_case($object_name));
                    ?>
    public function get_child_<?=$object_name_for_getter?>($sort = NULL);
                    <?
                }
            }
        }

    }
?>

}
