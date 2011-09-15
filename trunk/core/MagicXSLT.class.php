<?php 
class MagicXSLT{
	
	static public function smarty_transform($xml,$xsl_file,$path=false){
		$xml = trim($xml);
		if($path === false){
			$xsl_file = (DIR_APP . "/xsl/" . $xsl_file);
		}else{
			$xsl_file = ($path . "/" . $xsl_file);
		}
		if(file_exists($xsl_file)){
			$xsl = file_get_contents($xsl_file);
			return self::transform(
				$xml,
				$xsl
			);	
		}else{
			return "Cannot load XSL file: $xsl_file ";
		}
		
	}
	static public function transform($xml,$xsl){
		$xslt = new XSLTProcessor(); 
		$xslt->importStylesheet(new  SimpleXMLElement($xsl)); 
		return $xslt->transformToXml(new SimpleXMLElement($xml));
	} 
}