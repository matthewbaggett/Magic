<?php

class MagicApplication {
	static protected $instance = NULL;
	static public $config;
	public $painter;
	public $page;
	static public $settings;
	protected $object_factory;
	public $database;
	public $app_root;
	static public $objects = null;
	static public $schema = null;
	static public $nocache = false;
	static public $actionlog = true;

	private $time_startup;

	static public function &get_singleton() {
		return self::$singleton;
	}

	public static function Factory($config) {
		if (self::$instance === NULL) {
			self::$instance = new Application($config);
		}
		return self::GetInstance();
	}

	public static function GetInstance() {
		return self::$instance;
	}
	
	/**
	 * Check to see if a Cache Hit is applicable.
	 * Checks to see if:
	 *   - The file exists
	 *   - The file hasn't expired
	 *   - The user is NOT logged in (don't show logged in users cached content)
	 *   - The request is not a $_POST
	 */
	private function checkCacheGet(){
		$cache_path = $this->cachePath();
		
		// Check we're not being run by PHP-CLI
		if(php_sapi_name() == 'cli'){
			return FALSE;
		}
		// Do we have a cached version?
		if(!file_exists($cache_path)){
			MagicLogger::log("No cache file exists at {$cache_path}.");
			return FALSE;
		}
		// Has the cached file expired?
		if(filemtime($cache_path) < strtotime('1 hour ago')){
			MagicLogger::log("File at {$cache_path} too old.");
			return FALSE;
		}
		// No caching if the user is logged in.
		if($this->checkCacheGetHasUser()){
			MagicLogger::log("User is logged in, not serving cached file {$cache_path}");
			return FALSE;
		}
		// No caching if there is a POST operation going on
		if(count($_POST) > 0){
			MagicLogger::log("Request is a POST, not serving {$cache_path}");
			return FALSE;
		}
		// None of the above true? We can served a cached file.
		return TRUE;
	}
	
	private function checkCachePut(){
		$cache_path = $this->cachePath();
		
		// Nocache variable set?
		if(Application::$nocache == true){
			MagicLogger::log("Page set to NOCACHE");
			return FALSE;
		}
		// No caching if the user is logged in.
		if($this->checkCacheGetHasUser()){
			MagicLogger::log("User is logged in, not SAVING cached file {$cache_path}");
			return FALSE;
		}
		// No caching if there is a POST operation going on
		if(count($_POST) > 0){
			MagicLogger::log("Request is a POST, not SAVING {$cache_path}");
			return FALSE;
		}
		// Check to see if ENABLE_HTML_CACHE is enabled
		if(!SettingController::get('ENABLE_HTML_CACHE')){
			MagicLogger::log("Caching disabled in ENABLE_HTML_CACHE setting");
			return FALSE;
		}
		
		
		// If all clear...
		return TRUE;
	}

	private function checkCacheGetHasUser(){
		if(isset($_SESSION['user'])){
			if($_SESSION['user']->get_id() > 0){
				return true;
			}
		}
		return false;
	}
	
	private function cacheHit(){
		$cache_path = $this->cachePath();
		
		// Prepare ETAG values
		$lastmod = gmdate('D, d M Y H:i:s \G\M\T',filemtime($cache_path));
		$etag = sha1($cache_path."-".md5_file($cache_path));
		
		// Do we have an ETAG?
		$ifmod = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $lastmod : null; 
		$iftag = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] == $etag : null; 
		
		if (($ifmod || $iftag) && ($ifmod !== false && $iftag !== false)) { 
		    header('HTTP/1.0 304 Not Modified'); 
		} else {
		    header("Last-Modified: $lastmod"); 
		    header('ETag: "'.$etag.'"');
		    header("Expires: " . gmdate("D, d M Y H:i:s", strtotime("6 hours")) . " GMT");
		}
		
		
		MagicLogger::log("Cache Hit :) - {$cache_path}");
		header("MagicCacheRead: Read from cache at " . date("Y/m/d H:i:s") . " {$cache_path}.");
		echo file_get_contents($cache_path);
		
		exit;
	}
	
	public function cachePut($buffer){
		if($this->checkCachePut() && strlen(trim($buffer)) > 0){
			$buffer = $buffer . "\n<!-- Cache PUT at " . date("Y/m/d H:i:s") . "-->\n";
			mkdir(dirname($this->cachePath()),0777,true);
			file_put_contents($this->cachePath(), $buffer);
			MagicLogger::log("Logged to file: {$this->cachePath()}");
		}else{
			MagicLogger::log("Disallowed from writing cache file");
		}
		
		return $buffer;
	}
	
	private function cachePath(){
		return DIR_TEMP."/html_cache/".$_SERVER['QUERY_STRING'].'.html';
	}
	public function __construct() {
		// Do initialisation stuffs
		if(PHP_SAPI != 'cli'){
			session_set_cookie_params(2419200,"/");
			session_start();
			set_time_limit(90);
		}else{
			self::$actionlog = false;
		}
		$this->time_startup = microtime(true);
		
		// Initialisation complete
		
		// Check to see if we can hit the cache
		if($this->checkCacheGet()){
			// If yes, hit that thang!
			$this->cacheHit();
		}else{
			// If not, generate the page
			$this->app_root = MagicApplicationConfiguration::Factory()->app_root;
			MagicPerformanceLog::mark("MagicApplication __construct()");
			if(self::$config->database === null){
				die("Sorry, cannot boot. I have no configuration.\n");
			}
			MagicDB::$database = MagicDatabase::Factory()->boot(self::$config->database);
			
			if(!MagicDB::$database instanceof MagicDatabase){
				die("Sorry, cannot boot. Could not connect to database.\n");
			}
			
			self::$actionlog = false;
			if(class_exists("SettingController")){
				if(SettingController::get("ACTIONLOG_ENABLED") == 1){
					self::$actionlog = true;
				}
			}
		}
		
	}

	public function routing() {
		if (isset($_REQUEST['controller']) && isset($_REQUEST['method'])) {
			$controller = $_REQUEST['controller'];
			
			// Experimental: singularise the controller
			$controller_singular = Inflect::singularize($controller) . "Controller";
			$controller_plural   = Inflect::pluralize($controller)   . "Controller";

			// Decide if there is a controller named like this...
			if(class_exists($controller_plural, true)){
				$controller_to_call = $controller_plural;
			}elseif(class_exists($controller_singular, true)){
				$controller_to_call = $controller_singular;
			}else{
				throw new exception("Cannot find a controller that matches either \"{$controller_plural}\" or \"{$controller_singular}\". D: ");
			}
			
			$method = $_REQUEST['method'];
			if(!strlen(trim($method)) > 0){
				$method = 'default';
			}
			$method_to_call = MagicApplication::methodise($method);
			MagicPerformanceLog::mark("Getting controller");
			$oController = $controller_to_call::Factory();
			MagicPerformanceLog::mark("Check method exists");
			MagicLogger::log("Calling {$controller_to_call}->{$method_to_call}()...");

			if (method_exists($oController, $method_to_call)) {
				MagicLogger::log("Method does exist: {$controller_to_call}->{$method_to_call}()!");
								
				$this->page->template = strtolower(Inflect::singularize($controller).".{$method}.tpl");
				//call_user_method($method_to_call, $oController);
				$this->page->method = $method;
				$this->page->controller = $controller;
				if(method_exists($oController, 'AllActions')){
					call_user_func(array($oController, 'AllActions'),$method);
				}
				MagicPerformanceLog::mark("Call function \"{$controller_to_call}->{$method_to_call}()\"");
				call_user_func(array($oController, $method_to_call));
				MagicPerformanceLog::mark("\"{$controller_to_call}->{$method_to_call}()\" ended");
				MagicLogger::log("Routing complete");
				$this->page->route = new stdClass();
				$this->page->route->controller = strtolower($controller);
				$this->page->route->method = strtolower($method);
				
			} else {
				MagicLogger::log("No such Controller/Method: {$controller_to_call}->{$method_to_call}()!");
				
				throw new MagicException("Cannot find this page :(", "There is no method {$method} ({$method_to_call}) on controller {$controller} ({$controller_to_call})");
			}
		} else {
			$this->defaultAction();
		}
	}

	public function route() {
		// Start the buffer...
		ob_start(array($this,'cachePut'));
		
		/**
		 * CANONICALISE STAGE
		 */
		// Check to see if Canonicalisation is enabled.. Usually is!
		if(SettingController::get("CANONICALISATION_ENABLED") == 1){
			// Check to see if we SHOULD canonicalise...
			if(MagicUtils::canonicalisationAppropriate()){
				MagicPerformanceLog::mark("Canonicalising");
				// Check to see if the canonical URL matches THIS request.
				if(MagicUtils::canonical() != MagicUtils::thisurl()){
					// If it doesn't, redirect!
					MagicUtils::canonicalise();
				}
			}
		}
		
		/**
		 * ANALYTICS PHASE
		 */
		$this->request_analytics();
		
		/**
		 * ROUTING PHASE
		 * 
		 * All of the heavy lifting and application code is executed in this phase.
		 */
		MagicPerformanceLog::mark("pre routing()");
		$this->routing();
		MagicPerformanceLog::mark("post routing()");
		
		// Push some useful things ifnto the page's painter...
		$app_instance = MagicApplication::GetInstance();
		$app_instance->page_setup_specific();
		
		$this->page->query_log = MagicDatabase::$log;
		$this->painter->assign("page", $this->page);
		$this->painter->assign("time_at_execute_start", $this->time_startup);
		$this->painter->assign("time_at_execute_end", microtime(true));
		
		/**
		 * RENDER PHASE
		 */
		// Call the renderer...
		MagicPerformanceLog::mark("Smarty Render Called");
		// Work out which renderer mode to use...
		if ($this->page->layout) {
			$this->painter->render($this->page->layout);
		} elseif ($this->page->template) {
			$this->painter->render($this->page->template);
		} else {
			$this->painter->render();
		}
		// Render complete.
		MagicPerformanceLog::mark("Smarty Render Completed");
		// Spit out the generated time...
		echo "\n<!--";
		echo "  Generated at " . date('l jS \of F Y h:i:s A')."\n";
		echo "  Memory peak (sys/emalloc): ".floor(memory_get_peak_usage(true)/1024/1024)." M / ".floor(memory_get_peak_usage(false)/1024/1024)." M\n";	
		echo "-->\n";
		// Spit out the performance log...
		MagicPerformanceLog::get_instance()->render_log();
		// Flush into cache and pump to the user.
		ob_end_flush();
	}
	
	private function request_analytics(){
		// Check that the magic_id is valid:
		$cookie_is_valid = true;
		if(isset($_COOKIE['magic_id'])){
			if(VisitorSearcher::Factory()->search_by_id(base_convert($_COOKIE['magic_id'], 36,10))->count() == 0){
				$cookie_is_valid = false;
			}
		}
		#echo "Cookie is " . ($cookie_is_valid?'valid':'not valid');
		
		// Give the user a magic_id if they've not got one.
		if(!isset($_COOKIE['magic_id']) || $cookie_is_valid === false){
			//Look for Visitors from this location before...
			$oVisitor = VisitorSearcher::Factory()->search_by_ip($_SERVER['REMOTE_ADDR'])->execute_one();
			if($oVisitor === false ){
				$oVisitor = Visitor::Factory()
					->set_ip($_SERVER['REMOTE_ADDR'])
					->save();
			}
			$oVisitor = Visitor::Cast($oVisitor);
			$time_to_expire = strtotime("now + 4 weeks");
			setcookie("magic_id", $oVisitor->get_id36(), $time_to_expire,"/");
		}

		// Load their Visitor record...
		if($oVisitor === null){
			$oVisitor = VisitorSearcher::Factory()
				->search_by_id(base_convert($_COOKIE['magic_id'], 36,10))
				->execute_one();
		}
		
		$oVisitor = Visitor::Cast($oVisitor);
		if(isset($_SERVER['REDIRECT_URL'])){
			$path = $_SERVER['REDIRECT_URL'];
		}elseif(isset($_SERVER['REQUEST_URI'])){
			$path = $_SERVER['REQUEST_URI'];
		}else{
			$path = "unknown_path";
		}
		$oThisView = View::Factory()
			->set_visitor($oVisitor->get_id())
			->set_path($path)
			->set_accesstime(time())
			->save();
	}

	static public function methodise($method) {
		
		$method = explode("-", $method);
		foreach ($method as &$method_bit) {
			$method_bit = ucfirst($method_bit);
		}
		$method = implode("", $method);
		return $method . "Action";
	}

	public function page_setup(){
		$this->page_reset();
		
		// Initiate Node.JS Backbone
		if(SettingController::get('ENABLE_TURBO_CORE')){
			$this->page->site->scripts[] = "http://core.turbocrms.com:19659/jquery-1.6.2.js";
			$this->page->site->scripts[] = "http://core.turbocrms.com:19658/nowjs/now.js";
			$this->page->site->scripts[] = "http://core.turbocrms.com:19659/corebar.js";
		}
		
	}
	
	public function page_setup_specific(){
		$filename = $this->page->route->controller . "." . $this->page->route->method . ".js";
		$filepath = "/plugins/cms/resources/js/" . $filename;
		if(file_exists(ROOT . $filepath)){
			$this->page->site->scripts[] = $this->page->site->sys_root . $filepath;
		}
	}
	
	public function page_reset(){
		$this->page->site->scripts = array();
		$this->page->site->csses = array();
		$this->page->site->jses = array();
		$this->page->template = null;
		$this->page->layout = null;
	}
	
	protected function setup() {
		MagicLogger::init();
		if (FORCE_REGEN) {
			MagicCache::clear();
		}
		
		$this->object_factory = new MagicObjectFactory();
		$this->object_factory->check();
		self::$objects = $this->object_factory->get_list_of_objects();
		self::$schema = $this->object_factory->get_object_schema();

		$this->painter = MagicPagePainter::Factory();
		self::$settings = SettingSearcher::Factory()->execute();
		$this->page = new MagicPage();
		$this->page->site->template_root = ROOT . "template/";
		$this->page->site->app_root = $this->app_root;
		$this->page->site->sys_root = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
		$this->page->server = $_SERVER;
			
	}

	public function defaultAction() {
		MagicPerformanceLog::mark("noop");
	}

	public function testAction() {
		/*
		 * Run all the core tests
		 */
		require_once(ROOT . '/lib/simpletest/autorun.php');

		$test_files_app = MagicUtils::get_directory_list(DIR_APP . "/temp/tests");
		$test_files_core = MagicUtils::get_directory_list(ROOT . "/tests");
		$test_files = array_merge($test_files_core, $test_files_app);
		MagicLogger::log("Running tests");
		foreach ($test_files as $test_file) {
			require_once($test_file);
			$test_class = basename($test_file, ".test.php");
			$test = new $test_class();
			$test->setUp();
			unset($test);
		}
		exit;
		ob_start();
		foreach ($test_files as $test_file) {
			//echo "Test case file: $test_file\n";
			require_once($test_file);
			$test_class = basename($test_file, ".test.php");
			$test = new $test_class();
			$test->run(new MagicTestTextReporter());
			unset($test);
		}

		$test_results = ob_get_flush();
		//echo $test_results;

		Mail::Factory()->add_to("matthew@baggett.me")->set_subject(APPNAME . " automatic test results")->set_message("Test results generated for project " . APPNAME . "\n\n" . $test_results)->add_attachment($test_results, "test_results.txt")->send()->save();

	}

	public function cronAction() {
		/*
		 * Check to see if we should be running the cron.
		 */

		if(SettingController::get('CRON_ACTIVE') == 1){
			
			/*
			 * Find the PHP5.3 executable
			 */
			$php_to_run = MagicUtils::get_php_binary();
			//MagicLogger::log("PHP binary: {$php_to_run}");
			//$message_lines[] = "PHP binary: {$php_to_run}";
			/*
			 * Decide what to run
			 */
			$crons_to_run = array();
			$crons_to_run[] = "minute";
			$time_to_run_nightly = strtotime("today 00:00");
			$time_to_run_daily = strtotime("today 12:00");
			if (time() > $time_to_run_nightly && time() < $time_to_run_nightly + 60) {
				$crons_to_run[] = "nightly";
			}
			if (time() > $time_to_run_daily && time() < $time_to_run_daily + 60) {
				$crons_to_run[] = "daily";
			}
			if (date("i") == "00") {
				$crons_to_run[] = "hourly";
			}

			/*
			 * Gather files
			 */
			$files_to_run = array();
			if (in_array("minute", $crons_to_run)) {
				$files_to_run = array_merge($files_to_run, (array)MagicUtils::get_directory_list(DIR_APP . "/cron/minute"));
			}
			if (in_array("nightly", $crons_to_run)) {
				$files_to_run = array_merge($files_to_run, (array)MagicUtils::get_directory_list(DIR_APP . "/cron/nightly"));
			}
			if (in_array("daily", $crons_to_run)) {
				$files_to_run = array_merge($files_to_run, (array)MagicUtils::get_directory_list(DIR_APP . "/cron/daily"));
			}
			if (in_array("hourly", $crons_to_run)) {
				$files_to_run = array_merge($files_to_run, (array)MagicUtils::get_directory_list(DIR_APP . "/cron/hourly"));
			}
			//MagicLogger::log("Crons to run: " . implode(", ", $crons_to_run));

			/*
			 * Sort the files by name
			 */
			sort($files_to_run);

			/*
			 * Crunch through files
			 */
			foreach ($files_to_run as $file_to_run) {
				//Make the string that exec() is going to exec.
				$file_to_run = trim($file_to_run);
				$run = "{$php_to_run} {$file_to_run}";
				//Log the execution line
				MagicLogger::log("");
				MagicLogger::log("Executing: {$run}");
				$message_lines[] = "Executing: {$run}";
				//Execute...
				$this_run_output = array();
				exec($run, $this_run_output);

				//Log the execution result
				foreach ($this_run_output as $line) {
					$line = " > " . $line;
					echo $line . "\n";

					$message_lines[] = $line;
				}
				$message_lines[] = '';
			}

			//Add some more data about the crontab that just ran
			$message_lines[] = "Run on " . date("F j, Y, g:i a");
			$message_lines[] = "Crontab minute run - " . date("Y/m/d H:i:s");
			$message_lines[] = print_r($files_to_run, true);

			//Email the cron results
			if (MagicConfig::get("EMAIL_ON_EVERY_CRON") == "true") {
				Mail::Factory()->set_subject(APPNAME . " crontab - " . implode(", ", $crons_to_run))->set_message(implode("\n", $message_lines))->save()->send();
			}
			exit;
		}
	}

	public function backupAction() {
		$mof = new MagicObjectFactory();
		$date_stamp = date("Ymd.His");
		$new_mail = Mail::Factory()->set_to("backup@baggett.me")->set_subject(APPNAME . " backup run @ {$date_stamp}")->set_message("Attached is a dump of all the database tables in SQL format");

		foreach ((array) $mof->get_list_of_objects() as $object) {
			// First back up YML
			//MagicLogger::log("Backing up {$object}");
			//$result = call_user_func(array($object, 'backup_yql'));
			//echo "\rAttaching...";
			//$new_mail->add_attachment($result, "{$object}.{$date_stamp}.yml");
			//echo "\rOkay\r";

			// Now the SQL
			echo "\rGrabbing SQL...";
			$table_name = Inflect::pluralize($object);
			$tmp_file = ROOT_APP . "/temp/{$object}.{$date_stamp}.sql";
			$command = "mysqldump -h " . self::$config->database->host . " -u " . self::$config->database->username . ' -p' . self::$config->database->password . ' ' . self::$config->database->database . ' ' . $table_name . ' > ' . $tmp_file;
			exec($command);
			echo "\rAttaching SQL...";
			$new_mail->add_attachment_from_disk($tmp_file);

			echo "\rOkay\r";
		}
		echo "\rSending mail...";
		$new_mail->send()->delete();
		echo "\rSent!\n\n";
	}
	
	public function translateTaskAction(){
		MagicTranslate::checkForTranslations();
	}

	public function mailTaskAction() {
		MagicLogger::log("Getting unsent mails");
		$mails = MailSearcher::Factory()->search_by_sent(Mail::SENT_UNSENT)->sort("id", "desc")->limit(3)->execute();
		MagicLogger::log("Processing (" . count($mails) . ") unsent mails");
		foreach ((array)$mails as $mail) {
			$mail = Mail::Cast($mail);
			$mail->send();
		}
	}

	public function cronCompleteTaskAction(){
		$mail = Mail::Factory()
			->set_to("matthew@baggett.me")
			->set_subject(APPNAME . " nightly cron complete")
			->set_message(APPNAME . " nightly cron complete")
			->send();
	}

	static public function exception_handler_web(Exception $e, $no_cli = false) {
		if (!$no_cli) {
			MagicApplication::exception_handler_cli($e);
		}
		require_once(ROOT . "application/Exception/exception.php");
	}

	static public function exception_handler_cli(Exception $e) {
		MagicLogger::log("Uncaught exception: {$e->getMessage()}");
		foreach (explode("\n", $e->getTraceAsString()) as $trace_row) {
			MagicLogger::log("Trace: " . $trace_row);
		}
		if ($e instanceof MagicBaseException) {
			foreach (explode("\n", $e->getDetail()) as $detail_row) {
				MagicLogger::log("Detail: " . $detail_row);
			}
			foreach (explode("\n", print_r($e->getObject(), true)) as $object_row) {
				MagicLogger::log("Object: " . $object_row);
			}
		}

		if (class_exists("Mail")) {
			$mail = Mail::Factory();

			$pagepainter = new MagicPagePainter();
			$pagepainter->assign("heading","An uncaught exception has occurred");
			$pagepainter->assign("request_path",$_SERVER['SCRIPT_URI']);
			$pagepainter->assign("request_from",$_SERVER['REMOTE_ADDR']);
			$pagepainter->assign("request_browser",$_SERVER['HTTP_USER_AGENT']);
			$pagepainter->assign("message",$e->getMessage());
			$pagepainter->assign("trace",$e->getTraceAsString());
			$pagepainter->assign("environment",print_r($_ENV, true));
			$pagepainter->assign("mail_instance_id", $mail->get_mail_instance_id());

			if($e instanceof MagicExceptionInterface){
				$pagepainter->assign('detail',$e->getDetail());
			}
			
			try{
				$mail_message = $pagepainter->render("file://" . ROOT ."templates/email/mail.exception.tpl",false);
			}catch(Exception $e){
				die("Oh god, an exception occoured rendering the EXCEPTION?!?!");
			}

			$mail->set_subject("Exception: \"{$e->getMessage()}\"")
				->set_message($mail_message)
				->set_type(Mail::TYPE_HTML)
				->add_attachment(MagicLogger::get_log(), "MagicLogger." . time() . ".log")
				->save()
				->send();
		}
	}
}

if (PHP_SAPI != 'cli') {
	set_exception_handler(array('MagicApplication', 'exception_handler_web'));
} else {
	set_exception_handler(array("MagicApplication", "exception_handler_cli"));
}

