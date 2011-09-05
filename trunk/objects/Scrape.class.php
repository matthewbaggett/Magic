<?php

class Scrape
{
	private $post;
	private $extra_headers;
    private $document;
    private $xpath;
    public $html;
    
    private $ckfile;
    
    private $user_agent = 'Mozilla/5.0 (X11; U; Linux i686; en-GB; rv:1.9.2.18) Gecko/20110628 Ubuntu/10.10 (maverick) Firefox/3.6.18';

    public function __construct($url = null, $post = null, $extra_headers = null)
    {
    	if(!file_exists("/tmp/smile/")){
    		mkdir("/tmp/smile/");
    	}
    	$this->ckfile = "/tmp/smile/cookies.txt";
    	if ($post !== null){
    		$this->post = $post;
    	}
    	if ($extra_headers !== null){
    		$this->extra_headers = $extra_headers;
    	}
        if ($url !== null) {
            $this->go($url);
        }
    }
    
    static public function delete_cookies(){
    	unlink('/tmp/smile/cookies.txt');
    }
    public function go($url)
    {
        $this->document = $this->curl_get_page($url);
        $this->xpath = new DOMXpath($this->document);
    }

    public function get_element($element_identifier = "*/div[@id='yourTagIdHere']")
    {
        $elements = $this->xpath->query($element_identifier);
        if (!is_null($elements)) {
            return $elements;
        }else{
            return FALSE;
        }
    }

    public function curl_get_page($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, $this->ckfile); 
        //curl_setopt($ch, CURLOPT_COOKIEJAR, $this->ckfile);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_NOBODY, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        if(is_array($this->extra_headers)){
        	curl_setopt($ch, CURLOPT_HTTPHEADER, $this->extra_headers); 
        }
        if(is_array($this->post)){
        	print_r($this->post);
        	curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post);
        	curl_setopt($ch, CURLOPT_POST, true);
        }
        
        //echo " > Executing...";
        $this->html = curl_exec($ch);
        //echo " [DONE]\n";
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
		//echo " > Turning into DOMDocument...";
        $doc = new DOMDocument();
        @$doc->loadHTML($this->html);
        //echo " [DONE]\n";
        return $doc;

    }
}