<?php echo "<?php\n"; ?>
<?php include(ROOT . "/templates/system/txt.licence.php"); ?>

/**
 * Searcher generated <?=date("F j, Y, g:i:s a"); ?>.
 */

class <?=$this->name;?>Searcher extends MagicSearcher {
	const ROOT_OBJECT = '<?=$this->name;?>';

    const MODE_EQUALS = 'equals';
    const MODE_LIKE = 'like';
    const MODE_IN = 'in';
    const MODE_LESS_THAN = "<";
    const MODE_MORE_THAN = ">";
	
	protected $search_keys;

    protected $sort = NULL;
    protected $direction = NULL;
    protected $limit = NULL;
	
	public static function Factory(){
		return new <?=$this->name;?>Searcher();
	}
	
	public function __construct(){
		$this->search_keys = array();
		parent::__construct();
	}
	
	public function sort($sort,$direction = "ASC"){
		$this->sort = $sort;
		$this->direction = $direction;
		return $this;
	}
	
	public function limit($limit = 5){
		$this->limit = $limit;
		return $this;
	}
   
	<?php foreach($this->definition as $variable_name => $definition) { ?>
	
	public function search_by_<?=$variable_name;?>($search_term, $mode = <?=$this->name;?>Searcher::MODE_EQUALS){
		$this->search_keys[] = array('column' => '<?=$variable_name;?>', 'value' => $search_term, 'mode' => $mode);
		return $this;
	}
	<? } ?>

	/**
	 * Returns the sql string that this searcher is trying to call
	 *
	 * @return String 
	 */
    public function query(){
        return parent::query(<?=$this->name;?>Searcher::ROOT_OBJECT);
    }
	
	/**
	 * Return an array of <?=$this->name;?>.
	 *
	 * @return Array an array of <?=Inflect::pluralize($this->name);?>. 
	 */
	public function execute(){
		return parent::execute(<?=$this->name;?>Searcher::ROOT_OBJECT);
	}
	
	/**
	 * Return one <?=$this->name;?>.
	 *
	 * @return <?=$this->name;?> 
	 */
    public function execute_one(){
		return parent::execute_one(<?=$this->name;?>Searcher::ROOT_OBJECT);
	}
	
	/**
	 * Returns an integer count of <?=$this->name;?> that this searcher would find.
	 *
	 * @return integer count of searched <?=Inflect::pluralize($this->name);?>.
	 */
   	public function count($column_to_count = 'id'){
   		return parent::count($column_to_count, <?=$this->name;?>Searcher::ROOT_OBJECT);
   	}

   
}

