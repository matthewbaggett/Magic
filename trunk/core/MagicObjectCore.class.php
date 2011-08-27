<?php 
    class MagicObjectCore {
        const IS_DIRTY = 1;
        const IS_CLEAN = 0;
        const IS_UNSAVED = -1;

        protected $dirty = self::IS_UNSAVED;

        public function __construct () {}

        protected function set_dirty () {
            $this->dirty = self::IS_DIRTY;
        }

        public function core_save ($force_save = FALSE) {
            //MagicLogger::group("MOC::core_save()");
            switch ($this->dirty) {
                case self::IS_UNSAVED:
                    //MagicLogger::log("Unsaved");
                    $this->core_save_insert();
                    break;
                case self::IS_DIRTY:
                    //MagicLogger::log("Dirty");
                    if ($this->get_id() > 0) {
                        $this->core_save_update();
                    } else {
                        $this->core_save_insert();
                    }
                    break;
                case self::IS_CLEAN:
                    //MagicLogger::log("Clean");
                    if ($force_save) {
                        $this->core_save_update();
                    } else {
                        $this->core_save_skip();
                    }
                default:
                    throw new MagicException("Sorry, but this object has an invalid state. The state supplied is {$this->dirty}, and that is unacceptable. The acceptable states are listed inside the  objectio_state class.");
            }
            //MagicLogger::groupEnd();
            return $this;
        }

        public function core_load ($id = null) {
            $query = MagicQuery::Factory("SELECT",$this->_table)->addWhere('id', '=', $id);
            $results = $query->execute();
            $this->load_by_row(end($results));
            $this->dirty = self::IS_CLEAN;
            
        }

        private function core_save_insert () {

            $query = new MagicQuery("INSERT", $this->_table);

            foreach ((array) $this->core_table_columns() as $column) {
                switch ($column) {
                    case 'id':
                        break;
                    default:
                        $method = "get_db_safe_{$column}";
                        $value = $this->$method();
                        $query->addSet($column, $value);
                }
            }
            //MagicLogger::group("Insert");
            //MagicLogger::log($query->query());
            //MagicLogger::groupEnd();
            $query->execute();
            $this->set_id($query->get_insert_id());
            $this->set_dirty(self::IS_CLEAN);
            //echo "\nInserted. ID = $id\n\n";
			$this->post_save();
        }



        private function core_save_update () {
            $query = new MagicQuery("UPDATE", $this->_table);
            foreach ($this->core_table_columns() as $column) {
                switch ($column) {
                    case 'id':
                        break;
                    default:
                        //echo "Get \$this->{$column}\n";
                        $method = "get_db_safe_{$column}";
                        $value = $this->$method();
                        //MagicLogger::log("{$column} = {$value}");
                        $query->addSet($column, $value);
                }
            }
            $query->addWhere("id", "=", $this->get_id());
            //MagicLogger::group("Update");
            //MagicLogger::log($query->query());
            //MagicLogger::groupEnd();
            $query->execute();
			$this->post_save();
        }

        private function core_save_skip () {
			$this->post_save();
        }

		protected function post_save(){
			$this->dirty = self::IS_CLEAN;
		}

        private function core_table_exists () {
            $table_description = MagicDatabase::query(sprintf("DESCRIBE %s", $this->__objectio->table));
            if ($table_description != null) {
                //Table exists.
                return TRUE;
            } else {
                //Table does not exist.
                return FALSE;
            }
        }

        private function core_table_columns () {
            return explode("|", constant("{$this->_class}::MAGIC_OBJECT_CONTAINS"));
        }

        public function check_unique ($column, $value) {
            $matches = MagicQuery::Factory("SELECT", $this->_table)->addWhere($column, "=", $value)->execute();
            if (count($matches) > 0) {
                //Already exists
                return FALSE;
            }
            return TRUE;
        }

        public function validator_special_email ($email) {
            $isValid = TRUE;
            $atIndex = strrpos($email, "@");
            if (is_bool($atIndex) && !$atIndex) {
                $isValid = FALSE;
            } else {
                $domain = substr($email, $atIndex + 1);
                $local = substr($email, 0, $atIndex);
                $localLen = strlen($local);
                $domainLen = strlen($domain);
                if ($localLen < 1 || $localLen > 64) {
                    // local part length exceeded
                    $isValid = FALSE;
                } else if ($domainLen < 1 || $domainLen > 255) {
                    // domain part length exceeded
                    $isValid = FALSE;
                } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
                    // local part starts or ends with '.'
                    $isValid = FALSE;
                } else if (preg_match('/\\.\\./', $local)) {
                    // local part has two consecutive dots
                    $isValid = FALSE;
                } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
                    // character not valid in domain part
                    $isValid = FALSE;
                } else if (preg_match('/\\.\\./', $domain)) {
                    // domain part has two consecutive dots
                    $isValid = FALSE;
                } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
                    // character not valid in local part unless
                    // local part is quoted
                    if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
                        $isValid = FALSE;
                    }
                }
                if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
                    // domain not found in DNS
                    $isValid = FALSE;
                }
            }
            return $isValid;
        }

        public function load_by_row ($row) {
            foreach ($row as $column => $value) {
                $setter         = "set_raw_{$column}";
                $safe_setter    = "set_db_safe_{$column}";
                if (method_exists($this, $setter)) {
                    try{
                        if(method_exists($this,$safe_setter)){
                            call_user_func(array($this,$safe_setter),$value);
                        }else{
                            call_user_func(array($this,$setter),$value);
                        }
                    }catch(Exception $e){
                        MagicApplication::exception_handler_cli($e);
                    }
                }
            }
            $this->dirty = self::IS_CLEAN;
        }

        public function delete () {
            $q = MagicQuery::Factory("DELETE", $this->_table)
                    ->addWhere('id', '=', $this->get_id());
            //echo "******************************\n\n{$q->query()}\n********************************\n\n";
            $q->execute();
            return true;
        }

       public function get_object(){
         return $this;
      }
    }
