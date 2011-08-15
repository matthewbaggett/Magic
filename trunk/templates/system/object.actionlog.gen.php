<?php echo "<?php\n" ?>
<?php include(ROOT . "/templates/system/txt.licence.php") ?>
<?php $table_name = "ActionLog_" . Inflect::pluralize($this->name) ; ?>

<?php
echo MagicUtils::faerie("
                        Action Logger generated " . date("F j, Y, g:i:s a") . "
                        This class is to log the actions and events that occour on MagicObjects
                        ", 'action logger generator', 'demanding');
?>
/**
* Action Logger generated <?= date("F j, Y, g:i:s a")
; ?>.
*/

class <?= $this->name ?>ActionLogger extends MagicActionLogger implements MagicActionLoggerInterface, MagicObjectImplementation {

   protected $logged_element = '<?=$this->name?>';
   protected $_table = '<?=$table_name?>';
   protected $key;
   protected $variable;
   protected $before;
   protected $after;

   static public function Factory(){
      return new <?=$this->name?>ActionLogger();
   }

   public function set_<?=strtolower($this->name);?>_id($id){
      $this->key = $id;
      return $this;
   }

   public function save($force_save = false){
        if(($this->before != $this->after || $force_save == true) && Application::$actionlog == true){
            parent::save($force_save);
            return true;
        }else{
            return false;
        }
   }
}
