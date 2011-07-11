<?php
/****************************************************************************************
 * Implements SQL in a OOP-y way.                      *
 *  - Database agnostic                         *
 *  - Completely CRUDy http://en.wikipedia.org/wiki/Create,_read,_update_and_delete    *
 *  - M Baggett, Nov/Dec 2010 matthew@baggett.me               *
 ***************************************************************************************/
    /***************************************
     *   C.R.U.D:                     *
     *                              *
     *   Create            INSERT         *
     *   Read (Retrieve)      SELECT         *
     *   Update            UPDATE         *
     *   Delete (Destroy)   DELETE         *
     ***************************************/
    class MagicQuery {
        private $tables;
        private $joins;
        private $where;
        private $sets;
        private $columns;
        private $action = "SELECT";
        private $order = null;
        private $limit;
        private $group;
        public $rows_affected;
        private $column_to_key_by = null;

        private $generated_query;

		private $insert_id;

        //SELECT x,y,z FROM tablename WHERE foo = 'bar'
        public function __construct ($action = null, $table = null, $columns = null) {
            if ($action) $this->setAction($action);
            if ($columns) $this->columns = explode(",", $columns);
            if ($table) $this->addTable($table);
            return $this;
        }

        static public function Factory ($action = null, $table = null, $columns = null) {
            return new MagicQuery($action, $table, $columns);
        }

        public function addColumn ($column) {
            $this->columns[] = $column;
            return $this;
        }

        public function addColumns ($columns) {
            foreach ($columns as $column) {
                $this->addColumn($column);
            }
            return $this;
        }

        public function setGroup ($group) {
            $this->group = $group;
            return $this;
        }

        public function setAction ($action) {
            $this->action = strtoupper($action);
            return $this;
        }

        public function addTable ($table_name, $object_class = null) {
            $this->tables[] = $table_name;
            return $this;
        }

        public function addJoin ($table_a, $column_a, $table_b, $column_b) {
            $this->joins[] = array("table A" => $table_a, "column A" => $column_a, "table B" => $table_b, "column B" => $column_b);
            return $this;
        }

        public function addWhere ($column, $operation, $value, $escape = "yes") {
            if ($escape == "yes" || $escape == "no") {
                if ($escape == "yes") {
                    $value = MagicDB::escape($value);
                }
                $where_array = array('op' => $operation, 'value' => $value, 'escape' => $escape);
                $this->where[$column][] = $where_array;
            } else {
                throw new exception("addset has been passed a \$escape variable of '$escape'. The only acceptable input is (string) yes|no");
            }
            return $this;
        }

        public function addSet ($column, $value, $quotes = "yes") {
            if ($quotes == "yes" || $quotes == "no") {
                $this->sets[$column] = array("value" => $value, "add quotes" => $quotes);
            } else {
                throw new exception("addset has been passed a \$quotes variable of '$quotes'. The only acceptable input is (string) yes|no");
            }
            return $this;
        }

        public function addSets ($sets) {
            foreach ($sets as $column => $value) {
                $this->addSet($column, $value);
            }
            return $this;
        }

        public function setOrder ($order = '') {
            $this->order = $order;
            return $this;
        }

        public function setLimit ($limit) {
            $this->limit = $limit;
            return $this;
        }
        
        public function setKeyBy($column_to_key_by){
        	$this->column_to_key_by = $column_to_key_by;
        	return $this;
        }

        private function get_table_alias ($in) {
            $bits = explode(" ", $in, 2);
            if (count($bits) > 1) {
                return $bits[1];
            } else {
                return $in;
            }
        }

        private function query_mysql () {
            if (!$this->action) {
                $this->action = "SELECT";
            }
            switch (strtoupper($this->action)) {
                case "INSERT": //C - Create
                    $sql = $this->query_mysql_create();
                    break;
                case "SELECT": //R - Read
                    $sql = $this->query_mysql_read();
                    break;
                case "UPDATE": //U - Update
                    $sql = $this->query_mysql_update();
                    break;
                case "DELETE": //D - Destroy
                    $sql = $this->query_mysql_delete();
                    break;
            }
            return $sql;
        }

        private function query_mysql_create () {
            $sql = "INSERT INTO `{$this->tables[0]}`\n";
            foreach ($this->sets as $column => $container) {
                $columns[] = $column;
            }
            $sql .= "\t(`" . implode("`, `", $columns) . "`)\n";
            foreach ($this->sets as $column => $container) {
                $value = $container['value'];
                if ($container['add quotes'] == "no") {
                    $values[] = $value;
                } else {
                    $values[] = MagicDB::escape($value);
                }
            }
            $sql .= "VALUES\n";
            $sql .= "\t('" . implode("','", $values) . "')\n";
            return $sql;
        }

        private function query_mysql_read () {
            $whereSQL = array();
            //Preprocess the joins into wheres.
            if (count($this->joins) > 0) {
                foreach ($this->joins as $order => $join) {
                    $whereSQL[] = "{$join['table A']}.`{$join['column A']}` = {$join['table B']}.`{$join['column B']}`";
                }
            }
            //Start generating sql.
            $sql = "SELECT \n";
            if (count($this->columns) == 0) {
                $sql .= "\t*\n";
            } else {
                $sql .= "\t" . implode(",\n\t", $this->columns) . "\n";
            }
            $sql .= "FROM {$this->tables[0]} \n";
            if (count($this->tables) > 1) {
                foreach ($this->tables as $order => $table) {
                    if ($order != 0) {
                        $sql .= "JOIN $table\n";
                    }
                }
            }
            if (count($this->where) > 0) {
                foreach ($this->where as $column => $wheres) {
                    //print_r_html($wheres);
                    foreach ($wheres as $where) {
                        switch ($where['op']) {
                            case 'IN':
                                $whereSQL[] = "$column IN ('" . implode("', '", $where['value']) . "') ";
                                break;
                            default:
                                if ($where['escape'] == "yes") {
                                    $where['value'] = "'{$where['value']}'";
                                }
                                $whereSQL[] = "$column " . $where['op'] . " " . $where['value'] . "";
                        }
                    }
                }
            }
            if (count($whereSQL) > 0) {
                $sql .= "WHERE\n\t" . implode(" \n\tAND ", $whereSQL) . "\n";
            }
            //MySQL Group
            if ($this->group) {
                $sql .= "\nGROUP BY {$this->group}\n";
            }
            //MySQL Order
            if ($this->order == '') {
            } elseif ($this->order === null) {
                $sql .= "ORDER BY " . $this->get_table_alias($this->tables[0]) . ".constructedat DESC";
            } else {
                $sql .= "ORDER BY " . $this->order;
            }
            //MySQL Limit
            if ($this->limit) {
                $sql .= "\nLIMIT {$this->limit}";
            }
            return $sql;
        }

        private function query_mysql_update () {
            $sql = "UPDATE `{$this->tables[0]}` \n";
            if (count($this->sets)) {
                foreach ($this->sets as $column => $container) {
                    $value = $container['value'];
                    if ($container['add quotes'] == "no") {
                        $setSQL[] = "`$column` = $value";
                    } else {
                        $setSQL[] = "`$column` = '" . MagicDB::escape($value) . "'";
                    }
                }
                $sql .= "SET\n";
                $sql .= "\t" . implode(",\n\t", $setSQL) . "\n";
            }
            if (count($this->where) > 0) {
                foreach ($this->where as $column => $wheres) {
                    foreach ($wheres as $where) {
                        $whereSQL[] = "$column " . $where['op'] . " '" . $where['value'] . "'";
                    }
                }
                $sql .= "WHERE\n\t" . implode(" \n\tAND ", $whereSQL) . " ";
            }
            return $sql;
        }

        private function query_mysql_delete () {
            $sql = "DELETE FROM `{$this->tables[0]}` \n";
            if (count($this->where) > 0) {
                foreach ($this->where as $column => $wheres) {
                    foreach ($wheres as $where) {
                        $whereSQL[] = "$column " . $where['op'] . " '" . $where['value'] . "'";
                    }
                }
                $sql .= "WHERE\n\t" . implode(" \n\tAND ", $whereSQL) . " ";
            }
            return $sql;
        }

        public function execute ($object_class = null, $key_by_object_id = true) {
        	if(count($this->tables) == 0){
        		throw new MagicException("No tables specificed in Query");
        	}
            $sql = $this->query();
            //echo "<pre>Query:\n$sql</pre>\n";
            
            //$execution_time_at_start = microtime(true);
        	
            $results = MagicDB::Query($sql);
            if($this->action=="INSERT"){
				$this->insert_id = MagicDataBase::get_last_id();
			}
            if ($object_class == null) {
            	if($this->column_to_key_by === null){
                	$result = $results;
            	}else{
            		foreach ((array) $results as $result) {
            			$column_to_key_by = $this->column_to_key_by;
            			
            			$results_obj[$result->$column_to_key_by] = $result;
            		}
            		$result = $results_obj;
            	}
            } else {
                $results_obj = array();
                foreach ((array) $results as $result) {
                    $oThing = new $object_class();
                    $oThing->load_by_row($result);
                    if ($key_by_object_id === true || $this->column_to_key_by !== null) {
                    	if($this->column_to_key_by === null){
                        	$results_obj[$oThing->get_id()] = $oThing;
                    	}else{
                    		$results_obj[$oThing->$column_to_key_by] = $oThing;
                    	}
                    } else {
                        $results_obj[] = $oThing;
                    }
                }
                $result =  $results_obj;
            }
            
            //$execution_time_at_end = microtime(true);
            //$execution_time_total = $execution_time_at_end - $execution_time_at_start;
            //MagicLogger::group("Query on " . implode(", ",$this->tables) . " (" . round($execution_time_total,4) . " sec)");
            //MagicLogger::log($this->query(),"Query");
            //MagicLogger::log($execution_time_total,"Time to execute");
            //MagicLogger::log($result,"Result");
            //MagicLogger::groupEnd();
            return $result;
        }
        public function execute_one ($object_class = null) {
        	$this->setLimit(1);
        	$execution_result = $this->execute($object_class, false);
        	
            return end($execution_result);
        }
        public function execute_single_value () {
        	
            $arr = (array) $this->execute_one();
            return end($arr);
        }

        public function query () {
            //Only supports mysql right now so...
            $this->generated_query = $this->query_mysql();
            return $this->generated_query;
        }

        public function debug ($debugArray) {
            $this->rows_affected = $debugArray['rows_affected'];
        }

		public function get_insert_id(){
			return $this->insert_id;
		}
    }
