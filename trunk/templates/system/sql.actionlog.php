<?php 
	$number_of_variables = count($this->definition);
	$table_name = Inflect::pluralize($this->name);
?>
-- ALTER definition for <?=$this->name?>.
-- Table name is <?=$table_name?>.

CREATE TABLE  `ActionLog_<?=$table_name?>` (
   `id` INT( 10 ) NOT NULL AUTO_INCREMENT,
   `key_id` INT ( 10 ) NOT NULL ,
   `variable` VARCHAR( 255 ) NOT NULL ,
   `before` TEXT NOT NULL ,
   `after` TEXT NOT NULL ,
   `user_id` INT( 10 ) NOT NULL ,
   `timestamp` INT( 10 ) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE = MYISAM ;
