<?php

/*
 * Provides core support for things like database access.
 * 
 * @todo support json
 */
class Will_Core
{
	var $db;
	const AUTOINCREMENTS = ['mysql'=>"AUTO_INCREMENT", 'sqlite'=>"AUTOINCREMENT"];
	function  __construct()
	{
		$dsn=$this->get_dsn();
		$this->autoincrement_keyword=self::AUTOINCREMENTS[explode(":",$dsn['dsn'])[0]];
		try {
			$this->db = new PDO($dsn['dsn'], $dsn['user'], $dsn['password']);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			echo 'Connection failed: ' . $e->getMessage(). "\n";
			die;
		}
	}
	
	/**
	 * 
	 * @todo - put config params in external file
	 * 
	 */
	function get_dsn()
	{ 
//		   return ['dsn'=>'mysql:host=localhost;dbname=demo', 'user'=>'root', 'password'=>''];
		   return ['dsn'=>'sqlite:will_core_PDO.sqlite', 'user'=>'root', 'password'=>''];
	}

	function DB()
	{
		return $this->db;
	}
			
	const USERS_AUTOINCREMENT_START=1000000;
	
	const ADMIN_DEFAULT_PASSWORD='admin';
	
	const ACCOUNT_TYPE_ADMIN='ADMIN';
	const ACCOUNT_TYPE_USER='USER';
	
	const ACCOUNT_STATUS_NOMINAL='NOMINAL';
	const ACCOUNT_STATUS_BLOCKED='BLOCKED';
	const ACCOUNT_STATUS_RESTRICTED='RESTRICTED';
}


/*
 * Multi-featured return class
 */
class Will_Core_Return 
{
	function __construct($payload) 
	{
		$this->payload=$payload;
	}

	const RETURN_STATUS_OK='ok';
	const RETURN_STATUS_ERROR='error';

	var $payload;
}

?>



