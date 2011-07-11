<?php
class MagicActionLogger extends MagicObject
{

    public function save($force_save = false)
    {
        if ($_SESSION['user']) {
            $user_id = $_SESSION['user']->get_id();
        } else {
            $user_id = -1;
        }
        $log_query = MagicQuery::Factory("INSERT", $this->_table)
                ->addSet("key_id",$this->key)
                ->addSet("variable", $this->variable)
                ->addSet("before", $this->before)
                ->addSet("after", $this->after)
                ->addSet("user_id", $user_id)
                ->addSet("timestamp",time());
        //echo "doonk\n";
        //echo $log_query->query();
        $log_query->execute();
   }

    public function set_variable($variable)
    {
        $this->variable = $variable;
        return $this;
    }

    public function set_before($before)
    {
        $this->before = $before;
        return $this;
    }

    public function set_after($after)
    {
        $this->after = $after;
        return $this;
    }
}