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

   public function __construct() {
      $this->time_startup = microtime(true);
      set_time_limit(90);
      session_start(); 
      $this->app_root = MagicApplicationConfiguration::Factory()->app_root;
      MagicPerformanceLog::mark("MagicApplication __construct()");
      MagicDB::$database = MagicDatabase::Factory()->boot(self::$config->database);


   }

public function routing() {
      if (isset($_REQUEST['controller']) && isset($_REQUEST['method'])) {
         $controller = $_REQUEST['controller'];
         $controller_to_call = $controller . "Controller";
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
            $this->page->template = strtolower("{$controller}.{$method}.tpl");
            //call_user_method($method_to_call, $oController);
         	if(method_exists($oController, 'AllActions')){
            	call_user_func(array($oController, 'AllActions'),$method);
            }
            MagicPerformanceLog::mark("Call function \"{$controller_to_call}->{$method_to_call}()\"");
            call_user_func(array($oController, $method_to_call));
            MagicPerformanceLog::mark("\"{$controller_to_call}->{$method_to_call}()\" ended");
         } else {
            throw new MagicException("Cannot find this page :(", "There is no method {$method} on controller {$controller}");
         }
      } else {
      	 
         $this->defaultAction();
      }
   }

   public function route() {
   	  MagicPerformanceLog::mark("pre routing()");
      $this->routing();
      MagicPerformanceLog::mark("post routing()");
      $this->page->query_log = MagicDatabase::$log;
      $this->painter->assign("page", $this->page);
      $this->painter->assign("time_at_execute_start", $this->time_startup);
      $this->painter->assign("time_at_execute_end", microtime(true));
      MagicPerformanceLog::mark("Smarty Render Called");
      if ($this->page->template && $this->page->layout) {
         $this->painter->render($this->page->layout);
      } elseif ($this->page->template) {
         $this->painter->render($this->page->template);
      } else {
         $this->painter->render();
      }
      MagicPerformanceLog::mark("Smarty Render Completed");
      echo "\n<!-- Generated at " . date('l jS \of F Y h:i:s A') . "-->\n";
      MagicPerformanceLog::get_instance()->render_log();
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
      $this->page->layout = "index.tpl";
   }
   protected function setup() {
      MagicLogger::init();
      if (FORCE_REGEN) {
         MagicCache::clear();
      }
      $this->object_factory = new MagicObjectFactory();
      $this->object_factory->check();

      $this->painter = MagicPagePainter::Factory();
      self::$settings = SettingSearcher::Factory()->execute();
      $this->page = new MagicPage();
      $this->page->site->template_root = ROOT . "template/";
      $this->page->site->app_root = $this->app_root;
      $this->page->site->sys_root = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
       
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
      MagicLogger::log("Running cron at " . date("F j, Y, g:i a e") . " (" . time() . ")");
      /*
       * Find the PHP5.3 executable
       */
      $php_to_run = MagicUtils::get_php_binary();
      MagicLogger::log("PHP binary: {$php_to_run}");
      $message_lines[] = "PHP binary: {$php_to_run}";
      /*
       * Decide what to run
       */
      http: //www.bukkit.turbocrms.com/
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
      MagicLogger::log("Crons to run: " . implode(", ", $crons_to_run));

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

   public function backupAction() {
      $mof = new MagicObjectFactory();
      $date_stamp = date("Ymd.His");
      $new_mail = Mail::Factory()->set_to("backup@baggett.me")->set_subject(APPNAME . " backup run @ {$date_stamp}")->set_message("Attached is a dump of all the database tables in YML format");

      foreach ((array)$mof->get_list_of_objects() as $object) {
         MagicLogger::log("Backing up {$object}");
         $result = call_user_func(array($object, 'backup_yql'));

         $new_mail->add_attachment($result, "{$object}.{$date_stamp}.yml");
      }
      $new_mail->save()->send();
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
          $mail_message = $pagepainter->render("file://" . ROOT ."templates/email/mail.exception.tpl",false);

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
