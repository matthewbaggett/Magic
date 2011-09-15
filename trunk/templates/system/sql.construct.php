<?php 
	$number_of_variables = count($this->definition);
	$table_name = Inflect::pluralize($this->name);
?>

CREATE TABLE IF NOT EXISTS `<?=$table_name?>` (
	`DELETE_COLUMN` INT NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- Add the new columns
<?php 
foreach($this->definition as $variable_name => $definition) {
	//Process keys
	DB::Query( "ALTER TABLE  `{$table_name}` ADD  `{$variable_name}` INT NOT NULL ;\n");
	if($definition['key']){
		DB::Query( "ALTER TABLE `$table_name` ADD PRIMARY KEY (  `$variable_name` ) ; \n");
	}
}

// Clean up the construction column 
DB::Query("ALTER TABLE `<?=$table_name?>` DROP `DELETE_COLUMN`"); 
?> 

ALTER TABLE `Pages` DROP `DELETE_COLUMN`; 
