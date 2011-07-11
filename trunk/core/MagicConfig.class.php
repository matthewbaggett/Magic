<?php

class MagicConfig extends SettingController{
    static public function get($key){
        $item = SettingSearcher::Factory()->search_by_system_name($key)->execute_one();
        if(is_object($item)){
            if(strlen(trim($item->get_value())) > 0){
                return $item->get_value();
            }else{
                return $item->get_default_value();
            }
        }
        return false;
    }
}