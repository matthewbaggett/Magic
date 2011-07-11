<?php 
	class MagicDatabaseConfig
	{
		const PLATFORM_MYSQL = 'mysql';
		const PLATFORM_MSSQL = 'mssql';
		const PLATFORM_SQLITE = 'sqlite';

		const PLATFORM_MYSQL_ENGINE_INNODB = 'INNODB';
		const PLATFORM_MYSQL_ENGINE_MYISAM = 'MYISAM';

		public $platform = self::PLATFORM_MYSQL;
		public $host = 'localhost';
		public $port = 3306;
		public $username = 'root';
		public $password;
		public $database = 'magic';

		public $default_engine = self::PLATFORM_MYSQL_ENGINE_INNODB;
	}