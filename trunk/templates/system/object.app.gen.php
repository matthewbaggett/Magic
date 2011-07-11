<?php echo "<?php\n"; ?>
<?php include(ROOT . "/templates/system/txt.licence.php"); ?>

/**
 * Object generated <?=date("F j, Y, g:i:s a"); ?>.
 * App layer is provided so objects can be influenced at the application layer
 */
class <?=$this->name?>AppObject extends <?=$this->name?>CoreObject {
<?php
echo MagicUtils::faerie(
    "Generated class.
    You should override this class with your code for your application.
    Your overriding class should live at
    /application/".APPNAME."/objects/{$this->name}AppObject.class.php
    You can feel free to change the extends for this from {$this->name}CoreObject
    to another class if you want to seperate your code logic out into more classes",
    'code generator'
);
?>
}