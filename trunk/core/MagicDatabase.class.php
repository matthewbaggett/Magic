<?php 


class MagicDatabase {
   static $config;
   static $pdo;
   static $log;

   public function __construct(){
      MagicPerformanceLog::mark();
   }
   public function boot(MagicDatabaseConfig $config) {
      self::$config = $config;
      unset($config);
      switch (self::$config->platform) {
         case MagicDatabaseConfig::PLATFORM_MYSQL:
            try {
               
               $dns = "mysql:dbname=" . self::$config->database . ";host=" . self::$config->host . ";port=" . self::$config->port . ";charset=UTF-8";
               //echo "Connected to ".self::$config->database." on ".self::$config->host;
               try{
                  self::$pdo = new PDO($dns, self::$config->username, self::$config->password);
               }catch(PDOException $e){
               	  throw new MagicException("Cannot connect to database");
               }
               MagicPerformanceLog::mark("Connected to database!");
            } catch (Exception $e) {
               throw new MagicException("Uhoh. I cannot connect to the database with the details you've provided (db: ".self::$config->database." server: ".self::$config->host.":".self::$config->port." as ".self::$config->username."): {$e->getMessage()}");
            }
            break;
         default:
            throw new MagicException("Platform " . self::$config->platform . " is not available in The Magic Framework.");
      }
      MagicDatabase::query("SET `time_zone` = '".date('P')."'");
      return $this;
   }



   static function query($sql) {
      $log_entry = new MagicDatabaseLog();
      $log_entry->time_begin = microtime(true);
      $log_entry->query = $sql;
      $result = self::$pdo->query($sql);

      if (is_object($result)) {
         $results = array();
         while ($o = $result->fetch(PDO::FETCH_OBJ)) {
            $results[] = $o;
         }
      } else {
         $results = NULL;
      }
      $log_entry->time_end = microtime(true);
      self::$log[] = $log_entry;

      return $results;
   }

   static public function escape($text) {
      return self::escape_string($text);
   }

   static public function escape_text($text) {
      return self::escape_string($text);
   }

   static public function escape_string($text) {
      $text = addslashes($text);
      return $text;
   }

   /*
           * Escape booleans. Pretty obvious how.
           * Yes I'm aware they're not strictly bools... Bah.
           */
   static public function escape_bool($b) {
      if ($b === 0 || $b === FALSE) {
         return 0;
      } else {
         return 1;
      }
   }

   /*
           * This function escapes numbers by simply making sure its an integer first.
           */
   static public function escape_number($n) {
      if (is_numeric($n)) {
         return $n;
      } else {
         return false;
      }
   }

   /*
           * This was to escape an array of strings. Long story.
           */
   static public function escape_array($arr) {
      $newarr = array();
      foreach ($arr as $key => $element) {
         $newarr[$key] = self::escape($element);
      }
      return $newarr;
   }

   static public function Factory() {
      return new MagicDatabase();
   }

    static public function get_last_id(){
        return self::$pdo->lastInsertId();
    }
}

