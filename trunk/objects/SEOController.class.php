<?php 
class SEOController extends MagicBaseController{
	public function robotsAction(){
		Application::$nocache = true;
		header("Content-type: text/plain");
		echo "User-agent: *\n";
		echo "Disallow: /js/\n";
		echo "Disallow: /images/\n";
		echo "Disallow: /css/\n";
		echo "Sitemap: ".MagicUtils::thisdomain()."/sitemap.xml\n";
		
		exit;
	}
	
	public function sitemapAction(){
		
		// Generate the sitemap XML
		$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml.= '<urlset' . "\n";
    	$xml.= 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
    	$xml.= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
    	$xml.= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";
		$xml.= '<!-- Generated at '.date('Ymd H:i:s').' -->' . "\n";
		
		//Add Pages
		$pages = PageSearcher::Factory()->execute();
		foreach($pages as $oPage){
			$oPage = Page::Cast($oPage);
			$path = urlencode($oPage->get_path());
			$xml.= "<url>\n";
			$xml.= "<loc>".MagicUtils::thisdomain()."/Page/view/{$path}</loc>\n";
			$xml.= "</url>\n";
		}
		
		$xml.= '</urlset>';
		
		// Push out the sitemap XML
		header("Content-type: text/xml");
		echo $xml;
		exit;
	}
	
	public function fourohfourAction(){
		header("HTTP/1.0 404 Not Found");
		exit;
	}
	
	public function Factory(){
		return new SEOController();
	}
}
?>
