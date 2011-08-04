<?php 

class MagicPagePainter extends MagicSingleton {
   public $smarty;
   private $smarty_file_locations;
   private $init_run = false;
   protected static $singleton = null;

   public function get_singleton() {
      return self::$singleton;
   }

   public function __construct(){
           $this->init();
   }

   public function init() {
      $this->init_run = true;
      $this->smarty_file_locations['templates'] = DIR_APP . "/template";
      $this->smarty_file_locations['compiled'] = DIR_TEMP . "/smarty/compiled";
      $this->smarty_file_locations['cache'] = DIR_TEMP . "/smarty/cache";
      $this->smarty_file_locations['configuration'] = DIR_TEMP . "/smarty/configuration";
      foreach ($this->smarty_file_locations as $name => $file_location) {
         if (!file_exists($file_location)) {
            $mkdir_state = @mkdir($file_location, 0777, true);
            if ($mkdir_state === FALSE) {
               throw new MagicException("Failed to make directory: {$file_location}");
            }
         }
      }
      parent::init();
      $this->smarty = new Smarty();
      $this->smarty->setTemplateDir($this->smarty_file_locations['templates']);
      $this->smarty->setCompileDir($this->smarty_file_locations['compiled']);
      $this->smarty->setCacheDir($this->smarty_file_locations['cache']);
      $this->smarty->setConfigDir($this->smarty_file_locations['configuration']);

      $this->smarty->cache_lifetime = 300;
      $this->smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
      
      $this->smarty->debugging = FALSE;
      if($_SERVER['REMOTE_ADDR'] == '127.0.0.1'){
      	$this->smarty->debugging = TRUE;
      	$this->smarty->caching = Smarty::CACHING_OFF;
      }
      $this->smarty->allow_php_tag = TRUE;

      // $this->smarty->debugging = true;

      $this->assign("magic_web_root", MagicApplicationConfiguration::Factory()->web_root);
      $this->assign("magic_app_root", MagicApplicationConfiguration::Factory()->app_root);
   }

   public function assign($key, $value) {
      return $this->smarty->assign($key, $value);
   }

   public function render($file = "index.tpl", $output = true) {
      MagicLogger::log("Rendering {$file}...");

          if ($output) {
          	 header('Content-type: text/html; charset=utf-8');
             $this->smarty->display($file);
          } else {
             try{
                 $generated_data = $this->smarty->fetch($file);
             }catch(Exception $e){
                 echo "RenderException: " . $e->getMessage();
             }
             return $generated_data;
          }
      
   }
}
