<?php echo "<?php\n"; ?>
<?php include(ROOT . "/templates/system/txt.licence.php"); ?>

/**
 * Controller generated <?=date("F j, Y, g:i:s a"); ?>.
 */

class <?=$this->name?>CoreController extends <?=$this->name?>BaseController {
<?php
echo MagicUtils::faerie(
    "Generated controller.
    This should never contain code.
    This is only ever to be overridden by Core controllers, and they live in /objects.
    Not inside your app. Inside your app, you should override {$this->name}Controller.",
    'code generator'
);
?>
}
