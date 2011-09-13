<?php echo "<?php\n" ?>
<?php include(ROOT . "/templates/system/txt.licence.php") ?>
<?php $table_name = Inflect::pluralize($this->name); ?>

/**
 * Base Object generated <?=date("F j, Y, g:i:s a"); ?>.
 */

class <?=$this->name?>BaseObject extends MagicObject implements <?=$this->name?>Interface, MagicObjectImplementation {

	const TABLE = "<?=Inflect::pluralize($this->name)?>";
    protected $_table = "<?=Inflect::pluralize($this->name)?>";
    protected $_class = "<?=$this->name?>";
    protected $_logs = NULL;

<?php
	foreach($this->definition as $variable_name => $definition){
		if($definition['type'] == 'enum'){
			echo "\t/* Defined constants for {$variable_name} */\n";
			foreach($definition['enum'] as $enum_option){
				echo "\tconst ".strtoupper($variable_name)."_".strtoupper($enum_option)." = '{$enum_option}';\n";
			}
			echo "\n";
		}
	}
	echo "\n";
?>
	/* Const defining what variables are in this object */
   const MAGIC_OBJECT_CONTAINS = '<?=implode("|",array_keys($this->definition));?>';

<?php
	/*
	 * Loop over the variables, and write the variables for this item
	 */
	foreach($this->definition as $variable_name => $definition){
		/*
		 * Determine the default value
		 */
		if(isset($definition['foreign'])){
			$bits = explode(".",$definition['foreign']);
			$definition = $this->objectmap[$bits[0]][$bits[1]];
			unset($definition['key']);
		}
		
		switch($definition['type']){
			case 'enum':
				$default_value = "'{$definition['enum'][0]}'";
				break;
			case 'int':
			case 'integer':
				$default_value = 0;
				break;
			case 'text':
				$default_value = "''";
				break;
			case 'timestamp':
				$default_value = 0;
				break;
			default:
				$default_value = "NULL";
		}

		/*
		 * Generate the comment
		 */
		echo "\t/*\n";
		echo "\t * Variable: {$variable_name}\n";
		if(isset($definition['key'])){
			if($definition['key']){
				echo "\t * PRIMARY KEY\n";
			}
		}
		if(isset($definition['unique'])){
			if($definition['unique']){
				echo "\t * UNIQUE\n";
			}
		}
		if(isset($definition['type'])){
			if($definition['type']){
				echo "\t * Type: {$definition['type']}\n";

			}
		}
		if(isset($definition['length'])){
			if($definition['length']){
				echo "\t * Length: {$definition['length']}\n";
			}
		}
		echo "\t */\n";
		echo "\t private \${$definition['type']}_{$variable_name} = {$default_value};\n";
		echo "\n";
	}
	echo "\n";
	?>

   /*
    * Object constructor
    */
   public function __construct(){
        $this->_logs = array();
        parent::__construct();
   }
   
   /**
    * Throw back the Application Schema for <?=$this->name?>. 
    * @return Array 
    */
   static public function Schema(){
      return Application::$schema['<?=$this->name?>'];
   }

   <?php
	/*
	 * Loop over the variables again, make the getters and setters
	 */
	foreach($this->definition as $variable_name => $definition){
   ?>
   public function get_<?=$variable_name?>() {
      <? if($definition['serialize']){ ?>
         return MagicUtils::unserialize($this-><?=$definition['type']?>_<?=$variable_name?>);
      <? }else{ ?>
         return $this-><?=$definition['type']?>_<?=$variable_name?>;
      <? } ?>
   }

   public function get_db_safe_<?=$variable_name?>() {
      <? if($definition['serialize']){ ?>
         return $this-><?=$definition['type']?>_<?=$variable_name?>;
      <? }else{ ?>
         return $this-><?=$definition['type']?>_<?=$variable_name?>;
      <? } ?>
   }

   /**
    * Set the <?=$variable_name?> parameter, if its A) different to what it currently is
    * @return <?=$this->name?> 
    */
	public function set_<?=$variable_name?>($<?=$variable_name?>){
    	if($<?=$variable_name?> === $this-><?=$definition['type']?>_<?=$variable_name?>){
    		//If value is exactly equal, do nothing.
			return $this;
		}

		<? if($definition['type'] == 'enum'){?>
		if(!in_array($<?=$variable_name?>, array('<?=implode("', '",$definition['enum'])?>'))) {
			//Throw an exception if the ENUM is being set to an invalid value.
			throw new MagicException("Tried to set an ENUM on <?=$variable_name?> in <?=$this->name?> to ".var_export($<?=$variable_name?>).". This is not in the accepted range of <?=implode(", ",$definition['enum'])?>");
		}
      <? } ?>

      //Mark this object as dirty.
      $this->set_dirty();

      //Log the change
      $this->_logs[] = <?=$this->name?>ActionLogger::Factory()
         ->set_<?=strtolower($this->name);?>_id($this->get_id())
         ->set_variable('<?=$variable_name?>')
         ->set_before($this->get_<?=$variable_name?>())
         ->set_after($<?=$variable_name?>);

      //And finally set the value
      $this->set_raw_<?=$variable_name?>($<?=$variable_name?>);
      return $this;
   }

   protected function set_raw_<?=$variable_name?>($<?=$variable_name?>){
      <? if($definition['serialize']){ ?>
         $this-><?=$definition['type']?>_<?=$variable_name?> = MagicUtils::serialize($<?=$variable_name?>);
      <? }else{ ?>
         $this-><?=$definition['type']?>_<?=$variable_name?> = $<?=$variable_name?>;
      <? } ?>
   }

   <? if($definition['serialize']){ ?>
   public function set_db_safe_<?=$variable_name?>($<?=$variable_name?>){
      $this->set_<?=$variable_name?>(MagicUtils::unserialize($<?=$variable_name?>));
      return $this;
   }
   <? } ?>

	<? } ?>

   public function is_dirty_or_unsaved(){
        if($this->is_dirty() || $this->is_unsaved()){
            return true;
        }
        return false;
   }
   public function is_unsaved(){
        if($this->dirty == self::IS_UNSAVED){
            return true;
        }
        return false;
   }
   public function is_dirty(){
        if($this->dirty == self::IS_DIRTY){
            return true;
        }
        return false;
   }
   <?php
	/*
	 * Loop over the variables again, make the specific hash generators
	 */
	foreach($this->definition as $variable_name => $definition){
		if($definition['type'] == 'hash'){
		?>
			protected function generate_hash_<?=$variable_name?>(){
				<?

					foreach($definition['fields'] as $hash_variable){
					?>
						$hash_value[] = $this->get_<?=$hash_variable?>();

					<? } ?>
					$hash = hash("SHA1",implode(chr(0),$hash_value));
					$this->set_hash($hash);
			}
		<?
		}
	}

	/*
	 * Loop over the variables again, make the validators
	 */
	?>
	public function generate_hash(){
		<?
		foreach($this->definition as $variable_name => $definition) {
			if($definition['type'] == 'hash') {
			?>
				$this->generate_hash_<?=$variable_name?>();
			<?
			}
		}
		?>
	}


	<?
	/*
	 * Loop over the variables again, make the validators
	 */
	foreach($this->definition as $variable_name => $definition){
	?>

    /*
     * Validator for <?=$variable_name;?>. Max length = <?=$definition['length'];?>. Type = <?=$definition['type']?>.
     * Call this to check this parameter is valid. Returns true if object is valid.
     * @returns TRUE|FALSE
     */
    private function validate_<?=$variable_name;?>() {
        $errors = array();
        <?php //Type checks?>
        <?php if($definition['type'] == 'integer') { ?>
        if(!is_numeric($this-><?=$definition['type']?>_<?=$variable_name;?>) && $this-><?=$definition['type']?>_<?=$variable_name;?> !== NULL){
            $errors[] = new MagicValidationError("Variable <?=$variable_name;?> is not numeric, field is integer. Value passed: ".var_export($this-><?=$definition['type']?>_<?=$variable_name;?>,TRUE).".");
        }
        <?php }?>
        <?php if($definition['type'] == 'uuid') { ?>
        if(!UUID::is_valid($this-><?=$definition['type']?>_<?=$variable_name;?>) && $this-><?=$definition['type']?>_<?=$variable_name;?>){
            $errors[] = new MagicValidationError("Variable <?=$variable_name;?> is not a valid UUID. Value passed: ".var_export($this-><?=$definition['type']?>_<?=$variable_name;?>,TRUE).".");
        }
        <?php }?>
        <?php if($definition['type'] == 'timestamp') { ?>
        if(!is_numeric($this-><?=$definition['type']?>_<?=$variable_name;?>) && $this-><?=$definition['type']?>_<?=$variable_name;?> !== NULL){
            $errors[] = new MagicValidationError("Variable <?=$variable_name;?> is not timestamp, field is timestamp. Value passed: ".var_export($this-><?=$definition['type']?>_<?=$variable_name;?>,TRUE).".");
        }
        <?php }?>
        <?php if($definition['type'] == 'text') { ?>
        //TODO: Text validator
        <?php }?>

        <?php //Maximum Length. ?>
        <?php if($definition['length'] > 0) { ?>
        if(strlen($this-><?=$definition['type']?>_<?=$variable_name;?>) > <?=$definition['length'];?>){
            $errors[] = new MagicValidationError("Length of <?=$variable_name;?> too long. Value passed: ".var_export($this-><?=$definition['type']?>_<?=$variable_name;?>,TRUE).". ");
        }
        <?php } ?>

        <?php //Minimum Length?>
        <?php if($definition['min-length'] > 0) { ?>
        if(strlen($this-><?=$definition['type']?>_<?=$variable_name;?>) < <?=$definition['min-length'];?>){
            $errors[] = new MagicValidationError("Length of <?=$variable_name;?> too short. Value passed: ".var_export($this-><?=$definition['type']?>_<?=$variable_name;?>,TRUE).". ");
        }
        <?php } ?>

        <?php //Uniqueness check?>
        <?php if($definition['unique']){ ?>
        if(!$this->check_unique('<?=$variable_name;?>', $this-><?=$definition['type']?>_<?=$variable_name;?>)){
            $errors[] = new MagicValidationError("<?=$variable_name;?> is not unique. Value passed: ".var_export($this-><?=$definition['type']?>_<?=$variable_name;?>,TRUE).". ");
        }
        <?php } ?>

        <?php //Special validators?>

        <?php if($definition['validator']) { ?>
            <?php foreach((array) $definition['validator'] as $validator) { ?>
			if(method_exists(<?=$this->name?>,'validator_special_<?=$validator?>')){
				if(!<?=$this->name?>::validator_special_<?=$validator?>($this->get_<?=$variable_name;?>())){
					$errors[] = new MagicValidationError("<?=$variable_name;?> failed special validator 'validator_special_<?=$validator?>': '{$this->get_<?=$variable_name;?>()}'. ");
				}
			}
            <?php } ?>
        <?php } ?>

        return $errors;
    }
	<?php

	}

	/*
	 * Loop over the variables one last time to generate a validate() function for this object.
	 */
	foreach($this->definition as $variable_name => $definition){
		$validators[] = '$this->validate_' . $variable_name . '()';
	}
	?>
	/**
	 * Grand validator. Call this to call all of the other validation functions. Returns true if object is valid.
	 * @returns TRUE|FALSE
	 */
	public function validate(){
		$this->errors = parent::validate();
		<? foreach($validators as $validator){ ?>
			$this->errors = array_merge($this->errors, (array) <?=$validator;?>);
		<? } ?>
		$this->errors = array_filter($this->errors);
		if(count($this->errors) > 0){
			return FALSE;
		}else{
			return TRUE;
		}

	}

	/**
	 * Saver. Validates, then calls parent::save()
	 */
	public function save($validate = TRUE){

        if($this->dirty == <?=$this->name?>::IS_DIRTY || $this->dirty == <?=$this->name?>::IS_UNSAVED){

            if($validate === TRUE){
                if($this->validate()){
                    $this->save_log();
                    parent::save();
                }else{
                    foreach($this->errors as $error){
                        $error_messages[] = $error->error;
                    }
                    throw new MagicException("Validator on ".get_called_class()." failed. " . implode(", ", $error_messages). ".","Sorry, something in the data you've (or the system) provided is invalid or inacceptable.",$this->errors);
                }
            }else{
                $this->save_log();
                parent::save();
            }
        }
        return $this;
	}

   private function save_log(){
      if(is_array($this->_logs)){
         if(count($this->_logs) > 0){
            $logs_saved = 0;
            foreach($this->_logs as $log){
               $did_log_save = $log->save();
               if($did_log_save){
                   $logs_saved++;
               }
            }
            //echo "Saved $logs_saved logs\n";
         }
      }
   }

<?
    foreach($this->definition as $variable_name => $definition){
        if(isset($definition['foreign'])){
            $bits = explode(".",$definition['foreign']);
            $foreign_class = $bits[0];
            $foreign_variable = $bits[1];
            //$definition = $this->objectmap[$foreign_class][$foreign_variable];
            //unset($definition['key']);

?>
    /**
     * Throws out the parent <?=$foreign_class?> object. There will only be one.
     * @return <?=$foreign_class?> .
     */
	public function get_parent_<?=Inflect::singularize(strtolower($foreign_class))?>(){
        $result = <?=$foreign_class?>Searcher::Factory()
                ->search_by_<?=$foreign_variable?>($this->get_<?=$variable_name?>())
                ->execute_one();
        if(!$result instanceof <?=$foreign_class?>){
        	throw new exception("get_parent_<?=Inflect::singularize(strtolower($foreign_class))?>() could not find a <?=$foreign_class?> with <?=$foreign_variable?> of {$this->get_<?=$variable_name?>()}.");
        }
        return <?=$foreign_class?>::Cast($result);
    }
    
    /**
     * Throws out the parent <?=$foreign_class?> objects. There will be an array of them.
     * @return Array An array of <?=$foreign_class?> .
     */
	public function get_parent_<?=Inflect::pluralize(strtolower($foreign_class))?>(){
        $result = <?=$foreign_class?>Searcher::Factory()
                ->search_by_<?=$foreign_variable?>($this->get_<?=$variable_name?>())
                ->execute();
        return (array) $result;
    }

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

    /**
     * Bring back child <?=$object_name?> objects
     * @return Array of <?=$object_name?> .
     */
    public function get_child_<?=$object_name_for_getter?>($sort = NULL){
        $array = <?=$object_name?>Searcher::Factory()
                        ->search_by_<?=$variable_name?>($this->get_<?=$foreign_variable?>())
                        ->execute();
		if(count($array) == 0){
			throw new MagicException(
				"Sorry, you've tried to get something that doesn't exist!",
				"You tried to call get_child_<?=$object_name_for_getter?> on a <?=$this->name?> and it just doesn't have one!"
			);
		}
        if($sort !== NULL){
            MagicUtils::sort_objects($array,$sort);
        }
        return $array;
    }
    
    /**
     * Does this item have any child <?=$object_name?> objects?
     * @return Boolean
     */
    public function has_child_<?=$object_name_for_getter?>(){
    	try{
    		$this->get_child_<?=$object_name_for_getter?>();
    		return true;
    	}catch(Exception $e){
    		return false;
     	}
    }
    
    /**
     * Bring first child <?=$object_name?> object
     * @return <?=$object_name?> .
     */
      public function get_child_<?=Inflect::singularize($object_name_for_getter)?>($sort = NULL){
           return end(array_reverse($this->get_child_<?=$object_name_for_getter?>($sort)));
      }
                    <?
                }
            }
        }

    }
?>

}
