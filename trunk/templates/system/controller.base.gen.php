<?php echo "<?php\n"; ?>
<?php include(ROOT . "/templates/system/txt.licence.php"); ?>

/**
 * Base Controller generated <?=date("F j, Y, g:i:s a"); ?>.
 */


class <?=$this->name?>BaseController extends MagicController {
        public function Factory(){
            return new <?=$this->name?>Controller();
        }
}