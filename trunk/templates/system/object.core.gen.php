<?php echo "<?php\n"; ?>
<?php include(ROOT . "/templates/system/txt.licence.php"); ?>

/**
 * Object Core generated <?=date("F j, Y, g:i:s a"); ?>.
 * Core layer is provided so that objects can be influenced at the framework level.
 */

class <?=$this->name?>CoreObject extends <?=$this->name?>BaseObject implements <?=$this->name?>Interface {
<?php
echo MagicUtils::faerie(
    "Generated class.
    This should never contain code.
    This is only ever to be overridden by Core objects, and they live in /objects.
    Not inside your app. Inside your app, you should override {$this->name}AppObject.",
    'code generator'
);
?>
}