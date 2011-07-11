<?php echo "<?php\n"; ?>
<?php include(ROOT . "/templates/system/txt.licence.php"); ?>

/**
 * Controller generated <?=date("F j, Y, g:i:s a"); ?>.
 */

class <?=$this->name?>AppController extends <?=$this->name?>CoreController {
<?php
echo MagicUtils::faerie(
    "Generated controller.
    You should override this class with your code for your application.
    Your overriding class should live at
    /application/".APPNAME."/objects/{$this->name}AppController.class.php
    You can feel free to change the extends for this from {$this->name}CoreController
    to another controller if you want to seperate your code logic out into more controllers",
    'code generator'
);
?>
}
