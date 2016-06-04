<?php

require_once("will_core.php");

class Will_Core_Logging extends Will_Core
{
	function Will_Core_Logging($username='[not_set]', $ip_addr='[not_set]')
	{
		parent::__construct();
		$this->username=$username;
		$this->ip_addr=$ip_addr;
	}
	
	const LOG_EVENT='log_event';
	const LOG_LOG_IN='log_in';
	const LOG_LOG_OUT='log_out';
	const LOG_REGISTER='register';
	const LOG_LOG_IN_FAILURE='log_in_failure';
	const LOG_CHANGE_PASSWORD='change_password';
	const LOG_RESET_PASSWORD_REQUEST='reset_password_request';
	const LOG_RESET_PASSWORD='reset_password';
	const LOG_BLOCK_USER='block_user';
	const LOG_UNBLOCK_USER='unblock_user';
	

	/** 
	* writes out the log item 
	* 
	* @param const event_type to be logged 
	* @return int last inserted row_id
	*/
	private function simple_log($event_type)
	{
		$query = $this->DB()->prepare('INSERT INTO log (username, event, ip_addr)
			VALUES (:username, :event, :ip_addr)');
		$query->execute(['username'=>$this->username,'event'=>$event_type,'ip_addr'=>$this->ip_addr]);
		return $this->DB()->lastInsertId();
	}
	
	/** 
	* writes a log_in event
	* 
	* @return void
	*/
	function log_in()
	{
		return $this->simple_log(self::LOG_LOG_IN);
	}

	/** 
	* writes a log_in_failure event
	* 
	* @return void
	*/
	function log_in_failure()
	{
		return $this->simple_log(self::LOG_LOG_IN_FAILURE);
	}

	/** 
	* writes a log_out event
	* 
	* @return void
	*/
	function log_out()
	{
		return $this->simple_log(self::LOG_LOG_OUT);
	}

	/** 
	* writes a change_password event
	* 
	* @return void
	*/
	function change_password()
	{
		return $this->simple_log(self::LOG_CHANGE_PASSWORD);
	}

	/** 
	* writes a reset_password_request event
	* 
	* @return void
	*/
	function reset_password_request()
	{
		return $this->simple_log(self::LOG_RESET_PASSWORD_REQUEST);
	}

	/** 
	* writes a reset_password event
	* 
	* @return void
	*/
	function reset_password()
	{
		return $this->simple_log(self::LOG_RESET_PASSWORD);
	}

	/** 
	* writes a register event
	* 
	* @return void
	*/
	function register()
	{
		return $this->simple_log(self::LOG_REGISTER);
	}
	
	/** 
	* writes a free form event
	* 
	* @todo correct return
	* 
	* @param variadic $data	data items to be logged 	
	* @return void
	*/
	function freeform(...$data)
	{
		$event_data='';
		foreach ($data as $d)
		{
			$event_data.="$d ";
		}
		$query = $this->DB()->prepare('INSERT INTO log (event, event_data)
			VALUES (:event, :event_data)');
		$result = $query->execute(['event'=>self::LOG_EVENT,'event_data'=>$event_data]);
	}
	
	/** 
	* writes a block user event
	* 
	* @return void
	*/
	function block()
	{
		return $this->simple_log(self::LOG_BLOCK_USER);
	}

	/** 
	* writes an unblock user event
	* 
	* @return void
	*/
	function unblock()
	{
		return $this->simple_log(self::LOG_UNBLOCK_USER);
	}	
		
	/** 
	* dumps the log to the terminal
	* 
	* @return void
	*/
	function get_log_entry($row_id)
	{
		$query=$this->DB()->query("SELECT * from log where row_id=".$row_id);
		$result = $query->execute();
		return $query->fetch();
	}

	/** 
	* dumps the log to the terminal
	* 
	* @return void
	*/
	function dump_log()
	{
		$result=$this->DB()->query("SELECT * from log");		
		foreach ($result as $row)
		{
			print_r($row);	
		}
	}

}
