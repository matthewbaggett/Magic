<?php 
	$number_of_variables = count($this->definition);
	$table_name = Inflect::pluralize($this->name);
?>
-- ALTER definition for <?=$this->name?>.
-- Table name is <?=$table_name?>.
<? //print_r($this->definition); ?>
<?php 
	foreach($this->definition as $variable_name => $definition) {
		$i++; 
		//echo "ALTER TABLE  `{$table_name}` ADD  `{$variable_name}` INT NOT NULL ;\n";
		
		if(isset($definition['foreign'])){
			$bits = explode(".",$definition['foreign']);
			$definition = $this->objectmap[$bits[0]][$bits[1]];
            unset($definition['key']);
		}
		
		switch($definition['type']){
			case 'integer':
			case 'int':
				if(isset($definition['length'])){
					$type_string = "INT ( {$definition['length']} )";
				}else{
					$type_string = "INT";
				}
				break;
			case 'text':
				switch($definition['length']){
					case -1:
						$type_string = "TEXT";
						break;
					case $definition['length'] > 0:
						$type_string = "VARCHAR( {$definition['length']} )";
						break;
					default:
						$type_string = "VARCHAR( 1024 )";
				}
				break;
			case 'email':
				$type_string = "VARCHAR( 320 )";
				break;
			case 'enum':
				$type_string = "ENUM('" . implode("','",$definition['enum']) . "')";
				break;
			case 'timestamp':
				$timestamp_length = strlen(time());
				$type_string = "INT({$timestamp_length})";
				break;
            case "uuid":
                $uuid_length = strlen(UUID::v4());
                $type_string = "VARCHAR( {$uuid_length} )";
                break;
			case 'hash':
				$hash_length = strlen(hash("SHA1","test"));
				$type_string = "VARCHAR( {$hash_length} )";
				break;
			default:
				$type_string = "VARCHAR(512)";
				
		}
		
		
		//Process nulls
		if($defintion['null']){
			$is_null = "NULL";	
		}else{
			$is_null = "NOT NULL";
		}
		//Process Auto_increment
		if($definition['key']){
			$auto_increment = "AUTO_INCREMENT";
		}else{
			$auto_increment = '';
		}
		
?>
ALTER TABLE `<?=$table_name?>` CHANGE  `<?=$variable_name?>`  `<?=$variable_name?>` <?=$type_string?> <?=$is_null?> <?=$auto_increment?>;
<?php } ?>
