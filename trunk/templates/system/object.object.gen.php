<?php echo "<?php\n"; ?>
<?php include(ROOT . "/templates/system/txt.licence.php"); ?>

/**
 * {$this->name} Object Class generated <?=date("F j, Y, g:i:s a"); ?>.
 */

class <?=$this->name?>Object extends <?=$this->name?>AppObject implements <?=$this->name?>Interface {
<?php
    echo MagicUtils::faerie(
        "Watch this space",
        'forgetful',
        'lost'
    );
?>

}
