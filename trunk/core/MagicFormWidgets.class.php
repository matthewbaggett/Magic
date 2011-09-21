<?php
class MagicFormWidgets{
	static public function widget_select($params, $content, Smarty_Internal_Template $template, &$repeat){
		if(!isset($params['object']))	{ return "MagicFormWidgets::widget_select(): No object specified"; }
		if(!isset($params['display']))	{ return "MagicFormWidgets::widget_select(): No display column specified"; }
		if(!isset($params['name']))		{ return "MagicFormWidgets::widget_select(): No name specified"; }
		
		$objectSearcher = $params['object']."Searcher";
		$arrObject = $objectSearcher::Factory()->sort($params['display'], "ASC")->execute();
		$options = '';
		foreach($arrObject as $oObject){
			$value = call_user_method("get_{$params['value']}", $oObject);
			$label = call_user_method("get_{$params['display']}", $oObject);
			if(isset($params['selected'])){
				$selected = $params['selected'] == $value?'selected="selected" ':'';
			}else{
				$selected = '';
			}
			$options.= "<option value=\"{$value}\" {$selected}>";
			$options.= $label;
			$options.= "</option>\n";
		}
		return "<select name=\"{$params['name']}\" id=\"{$params['name']}\">\n{$options}\n</select>\n";
	}
	
}