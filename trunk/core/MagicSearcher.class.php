<?php
class MagicSearcher {

    public function __construct () {
    }

    private function build_selector ($root_object_class = NULL) {
        $query = new MagicQuery('SELECT', Inflect::pluralize($root_object_class));
        foreach ($this->search_keys as $search) {
            switch ($search['mode']) {
                case 'equals':
                    $mode = "=";
                    break;
                case 'like':
                    $mode = "LIKE";
                    break;
                case '>':
                    $mode = '>';
                    break;
                case '<':
                    $mode = '<';
                    break;
                case 'in':
                    $mode = "IN";
                    break;
                default:
                    throw new MagicException("Query mode '{$search['mode']}' is not a valid mode.");
            }
            $query->addWhere($search['column'], $mode, $search['value']);
        }
        if($this->sort !== NULL){
           //print_r($this);
           if(strtoupper($this->direction)=="DESC"){
              $order = "DESC";
           }else{
              $order = "ASC";
           }
           $query->setOrder($this->sort . " " . $order);
        }
        if($this->limit !== NULL){
            $query->setLimit($this->limit);
        }
        return $query;
    }
    private function prepare ($root_object_class = NULL) {
        $this->query = $this->build_selector($root_object_class);
    }

    public function execute ($root_object_class = NULL) {
        $this->prepare($root_object_class);
        return $this->query->execute($root_object_class);
    }

    public function query ($root_object_class = NULL) {
        $this->prepare($root_object_class);
        return $this->query->query();
    }

    public function count ($column_to_count = 'id', $root_object_class = NULL) {
        $query = $this->build_selector($root_object_class);
        $query->addColumn("count({$column_to_count}) as freq");
        $result = end($query->execute());
        return $result->freq;
    }

    public function execute_one ($root_object_class = NULL) {
        $result = $this->execute($root_object_class);
        return end(array_reverse($result));
    }
}

