<?php echo "<?php\n" ?>
require_once(ROOT . '/lib/simpletest/autorun.php');

<?php include(ROOT . "/templates/system/txt.licence.php") ?>
<?php $table_name = Inflect::pluralize($this->name); ?>
<?php $instance = "\$this->".strtolower($this->name); ?>

/**
 * <?=$this->name?> tests generated <?=date("F j, Y, g:i:s a"); ?>.
 */

class <?=$this->name?>Test extends UnitTestCase {

    function setUp() {
      <?=$instance?> = new <?=$this->name?>();
    }

    function tearDown() {
      unset(<?=$instance?>);
    }

    function testGetSet() {
        <?
		foreach($this->definition as $variable_name => $definition){
			$type = $definition['type'];
			if($type == 'text'){

				if(isset($definition['length'])) { $length = $definition['length']; }
				elseif($definition['length'] = -1){ $length = 2000; }
				else { $length = 255; }
				$dummy_data = str_pad($str,$length,"This is dummy text. ");
			?>

			$this->assertIsA(<?=$instance?>->set_<?=$variable_name?>('<?=$dummy_data?>'),'<?=$this->name?>', "try setting the data");
			$this->assertEqual(<?=$instance?>->get_<?=$variable_name?>(),'<?=$dummy_data?>',"Set data is same as get data");

			<?
			}

			if($type == 'int' || $type == "integer"){

				if(isset($definition['length'])) { $length = $definition['length']; }
				else { $length = 10; }
				$dummy_data = str_pad($str,$length,"42");
			?>

			$this->assertIsA(<?=$instance?>->set_<?=$variable_name?>('<?=$dummy_data?>'),'<?=$this->name?>', "try setting the data");
			$this->assertEqual(<?=$instance?>->get_<?=$variable_name?>(),'<?=$dummy_data?>',"Set data is same as get data");

			<?
			}
		}
		?>
    }
    function testMethods() {
<? foreach($this->definition as $variable_name => $definition){ ?>
        $this->assertTrue(method_exists(<?=$instance?>, 'get_<?=$variable_name?>'), "<?=$this->name?> has a method of 'get_<?=$variable_name?>'");
<?}?>
    }
    function testBeforeAlter(){
        $this->assertFalse(<?=$instance?>->is_dirty(),'Before altering, object should be clean');
        $this->assertTrue(<?=$instance?>->is_unsaved(),'Before altering, object should be unsaved');
    }
    function testAfterAlter(){
        <?=$instance?>->set_id(1);
        $this->assertTrue(<?=$instance?>->is_dirty(), 'After altering, object should be dirty');
    }

	function testSaveLoad(){
		$this->tearDown();
		$this->setUp();

		// Create and save a dummy item.
		<?=$instance?> = <?=$this->name?>::Factory()
			<?php
				foreach($this->definition as $variable_name => $definition){
					$data = false;
					switch($definition['type']){
						case 'text':
							$data = "'" . str_pad($null, $definition['length']?$definition['length']:255,"test_text_string_") . "'";
							break;
						
						case 'int':
							$min = 10*($definition['length']-1);
							$max = (10*$definition['length'])-1;
							$data = "rand({$min},{$max})";
							break;
					}
					if($data){
			?>
        		->set_<?=$variable_name?>(<?=$data?>)
			<?		} } ?>
				->save();

		// Now load out a clone from the DB
		$search_results = <?=$this->name;?>Searcher::Factory()
			->search_by_id(<?=$instance?>->get_id())
			->execute();
		
		//delete!
		foreach((array) $search_results as $search_result){
			$search_result->delete();
		}


		//Compare!
		$this->assertIdentical(<?=$instance?>,end($search_results),"Created and loaded object should be the same");
		print_r(<?=$instance?>);
		print_r($search_result);
	}
}