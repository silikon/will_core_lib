<?php

require_once("will_core.php");

class Will_Account extends Will_Core
{
	function Will_Account()
	{
		parent::__construct();
	}

	/**
	* hashes the password
	* 
	* @param string the password
	* @return string the hashed password
	*/
	private function our_hash_password($password)
	{
		return password_hash($password, PASSWORD_DEFAULT);
	}

	/** 
	* updates password 
	* 
	* @param string $username
	* @param string $password The new password
	* @return Will_Core_Return
	*/
	function update_password($username, $password): Will_Core_Return
	{
		$query = $this->DB()->prepare('update users set password=:password WHERE username=:username');
		$result = $query->execute(['username'=>$username, 'password'=>$this->our_hash_password($password)]);
		return new Will_Core_Return(['result'=>($query->rowCount()==1?"ok":"error")]);
	}

	/** 
	* creates, stores and returns an encoded version of a temporary 'reset' password
	* 
	* @param string $username
	* @return Will_Core_Return
	*/
	function create_reset_password(string $username): Will_Core_Return
	{
		//todo- store a creation time 
		$reset_password=$this->our_hash_password($username.time());
		$query = $this->DB()->prepare('update users set reset_password=:reset_password WHERE username=:username');
		$result = $query->execute(['username'=>$username, 'reset_password'=>$reset_password]);
		return new Will_Core_Return(['result'=>($query->rowCount()==1?"ok":"error"), 'reset_password'=>$reset_password]);
	}

	/** 
	* resets the password to the newly provided password if the reset_password matches
	* 
	* @param string $username
	* @param string $password The new password
	* @param string $reset_password The new password
	* @return Will_Core_Return
	*/
	function reset_password($username, $password, $reset_password): Will_Core_Return
	{
		$query = $this->DB()->prepare('update users set reset_password="",
			password=:password WHERE username=:username and reset_password=:reset_password');
		$result = $query->execute(['username'=>$username, 'password'=>$this->our_hash_password($password), 
			'reset_password'=>$reset_password]);
		return new Will_Core_Return(['result'=>($query->rowCount()==1?"ok":"error")]);
	}
	
	/** 
	* gets the full account info for the specified user
	* 
	* @param string $username
	* @return Will_Core_Return
	*/
	function get_account_info($username): Will_Core_Return
	{
		$query = $this->DB()->prepare("select * from users where username=:username");
		$result = $query->execute(['username'=>$username]);
		$account_info = $query->fetch();
		return new Will_Core_Return(['result'=>(gettype($account_info)!='boolean'?"ok":"error"),'account_info'=>$account_info]);
	}
	
	/** 
	* returns the specified column $ycol for the column $xcol with value $ybal
	* 
	* intended to be used for unique rows but doesn't yet limit that
	* 
	* @param string $xcol The column to select on
	* @param string $xval The value for selection column
	* @param string $ycol The column to return
	* @return Will_Core_Return
	*/
	private function __get_user_y_from_x($xcol, $xval, $ycol): Will_Core_Return
	{
		$query = $this->DB()->prepare("select $ycol from users where $xcol=:xval");
		$result = $query->execute(['xval'=>$xval]);
		$row = $query->fetch();
		if (gettype($row)=='boolean')
		{
			$ret=new Will_Core_Return(['result'=>"error"]);
		}
		else
		{
			$ret=new Will_Core_Return(['result'=>"ok",$ycol=>$row[$ycol]]);
		}
		return $ret; 
	}
	
	/** 
	* returns the username matching the specified user_id
	* 
	* @param integer $user_id
	* @return Will_Core_Return
	*/
	function get_username_from_id($user_id): Will_Core_Return
	{
		return $this->__get_user_y_from_x('user_id', $user_id, 'username');
	}
		
	/** 
	* returns the id matching the specified username
	* 
	* @param string $username
	* @return Will_Core_Return
	*/
	function get_id_from_username($username): Will_Core_Return
	{
		return $this->__get_user_y_from_x('username', $username, 'user_id');
	}
		
	/** 
	* returns the username matching the specified email
	* 
	* @param string $email
	* @return Will_Core_Return
	*/
	function get_username_from_email($email): Will_Core_Return
	{
		return $this->__get_user_y_from_x('email', $email, 'username');
	}	
		
	/** 
	* returns the email matching the specified username
	* 
	* @param string $username
	* @return Will_Core_Return
	*/
	function get_email_from_username($username): Will_Core_Return
	{
		return $this->__get_user_y_from_x('username', $username, 'email');
	}
	
	/** 
	* returns the account block status for the specified user
	* 
	* @param string $username
	* @return Will_Core_Return
	*/
	function get_user_account_status($username): Will_Core_Return
	{
		return $this->__get_user_y_from_x('username', $username, 'account_status');
	}
	
	/** 
	* blocks the specified user
	* 
	* @param string $username
	* @return Will_Core_Return
	*/
	function block_user($username): Will_Core_Return
	{
		return $this->update_account($username, ["account_status"=>Will_Core::ACCOUNT_STATUS_BLOCKED]);
	}
	
	/** 
	* unblocks the specified user
	* 
	* @param string $username
	* @return Will_Core_Return
	*/
	function unblock_user($username): Will_Core_Return
	{
		return $this->update_account($username, ["account_status"=>Will_Core::ACCOUNT_STATUS_NOMINAL]);
	}
	
	/** 
	* restricts the specified user
	* 
	* @param string $username
	* @return Will_Core_Return
	*/
	function restrict_user($username): Will_Core_Return
	{
		return $this->update_account($username, ["account_status"=>Will_Core::ACCOUNT_STATUS_RESTRICTED]);
	}

	/** 
	* validates the login for the given username and password
	* 
	* @param string $username
	* @param string $password
	* @return Will_Core_Return
	*/
	function validate_login($username, $password): Will_Core_Return
	{
		$password_check=$this->__get_user_y_from_x('username', $username, 'password');
		if ($password_check->payload['result']=='error')
		{
			return new Will_Core_Return(['result'=>"error",'reason'=>'user_not_found']);
		}
		if (!password_verify( $password,  $password_check->payload['password'] ))
		{
			return new Will_Core_Return(['result'=>"error",'reason'=>'bad_password']);
		}
		else
		{
			// password is ok, see if they are blocked
			$account_info=$this->__get_user_y_from_x('username', $username, 'account_status');
			if ($account_info->payload['account_status']===Will_Core::ACCOUNT_STATUS_NOMINAL)
			{
				return new Will_Core_Return(['result'=>"ok"]);
			}
			else
			{
				return new Will_Core_Return(['result'=>"error",'reason'=>'blocked']);
			}
		}
	}
	
	/** 
	* checks to see if specified email address is in use
	* 
	* @param string $email
	* @return Will_Core_Return
	*/
	function is_email_taken($email): Will_Core_Return
	{
		$query = $this->DB()->prepare("select count(*) as thecount from users where email=:email");
		$result = $query->execute(['email'=>$email]);
		$row = $query->fetch();
		if (gettype($row)=='boolean') // meaning, nothing found
		{
			return new Will_Core_Return(['result'=>"error"]);
		}
		else
		{
			return new Will_Core_Return(['result'=>"ok",'is_taken'=>($row['thecount']==1)]);
		}
	}

	/** 
	* checks to see if specified username is in use
	* 
	* @param string $username
	* @return Will_Core_Return
	*/
	function is_username_taken($username): Will_Core_Return
	{
		$query = $this->DB()->prepare("select count(*) as thecount from users where username=:username");
		$result = $query->execute(['username'=>$username]);
		$row = $query->fetch();
		if (gettype($row)=='boolean') // meaning, nothing found
		{
			return new Will_Core_Return(['result'=>"error"]);
		}
		else
		{
			return new Will_Core_Return(['result'=>"ok",'is_taken'=>($row['thecount']==1)]);
		}
	}
	
	
	/** 
	* applies the specified updates to the user account
	* 
	* does not allow password to be update using this function
	* 
	* @todo control specifically which columns can be allowed
	* 
	* @param string $username
	* @param array $updates Key/value pairs
	* @return Will_Core_Return
	*/
	function update_account($username, $updates): Will_Core_Return
	{
		$keys=array_keys($updates);
		$cnt=count($keys);

		try 
		{
			$this->DB()->beginTransaction();
			for ($i=0;$i<$cnt;$i++)
			{
				if ($keys[$i]=='password')
				{
					continue;
				}
				$sql="update users set ".$keys[$i]."=:".$keys[$i]." where username=:username ";
				$query=$this->DB()->prepare($sql);
				$query->execute(["username"=>$username,$keys[$i]=>$updates[$keys[$i]]]);
			}
			$this->DB()->commit();
			return new Will_Core_Return(['result'=>"ok"]);
		}
		catch(PDOException $ex) 
		{
			$this->DB()->rollBack();
			return new Will_Core_Return(['result'=>"error"]);
		}
  	}

	/** 
	* removes the specified user from the account
	* 
	* @param string $username
	* @return Will_Core_Return
	*/
	function remove_user($username): Will_Core_Return
	{
		$query=$this->DB()->prepare("delete from users where username=:username");
		$query->execute(["username"=>$username]);
		return new Will_Core_Return(['result'=>($query->rowCount()==1?"ok":"error")]);
	}

	/** 
	* create the initial tables
	* 
	* @todo add time zone
	* @todo add last login time
	* @todo add avatar
	* @todo address
	* 
	* @return Will_Core_Return
	*/
	function create_tables($drop=false)
	{
		if ($drop) $this->drop_tables();
		$retval=$this->DB()->exec( "CREATE TABLE users (user_id INTEGER PRIMARY KEY $this->autoincrement_keyword, 
									firstname TEXT, 
									lastname TEXT, 
									email VARCHAR(250) unique, 
									username VARCHAR(250) unique,  
									account_type TEXT, 
									password TEXT, 
									reset_password TEXT, 
									account_status TEXT,  
									datejoined DATETIME DEFAULT CURRENT_TIMESTAMP )");
		$retval=$this->DB()->exec(	"CREATE TABLE log (row_id INTEGER PRIMARY KEY $this->autoincrement_keyword,
									username TEXT, 
									event TEXT,
									event_data TEXT,
									ip_addr TEXT,
									log_time DATETIME DEFAULT CURRENT_TIMESTAMP )");
	}

	/** 
	* drop tables
	*
	* @return Will_Core_Return
	*/
	private function drop_tables()
	{
		$retval=$this->DB()->exec( "DROP TABLE IF EXISTS users");
		$retval=$this->DB()->exec(	"DROP TABLE IF EXISTS log");
	}

	/** 
	* adds the admin account
	* 
	* @todo allow optional setting of admin account username
	* 
	* @param string $password
	* @return Will_Core_Return
	*/
	function add_admin_account($password=Will_Core::ADMIN_DEFAULT_PASSWORD): Will_Core_Return
	{
		$username='admin';
		$query = $this->DB()->prepare('INSERT INTO users (user_id, account_type, username, account_status)
			VALUES (:user_id,:account_type,:username,:account_status)');
		$query->execute(["user_id"=>Will_Core::USERS_AUTOINCREMENT_START,
			"account_type"=>Will_Core::ACCOUNT_TYPE_ADMIN,
			"username"=>$username,
			"account_status"=>Will_Core::ACCOUNT_STATUS_NOMINAL]);
		if ($query->rowCount()!=1)
		{
			return new Will_Core_Return(['result'=>"error"]);
		}
		else
		{
			return $this->update_password($username, $password);
		}
	}


	/** 
	* creates a new user account with specified fields
	* 
	* @param string $firstname
	* @param string $lastname
	* @param string $email
	* @param string $username
	* @param string $password
	* @return Will_Core_Return
	*/
	function add_account($firstname, $lastname, $email, $username, $password): Will_Core_Return
	{
		$firstname=strip_tags(trim($firstname));
		$lastname=strip_tags(trim($lastname));
		$email=strip_tags(trim($email));
		$username=strip_tags(trim($username));
		$query = $this->DB()->prepare('INSERT INTO users 
			(firstname,lastname,email,username,account_status) VALUES
			(:firstname,:lastname,:email,:username,:account_status)');
		$hashedPass=$this->our_hash_password($password);
		$data = ["firstname"=>$firstname,"lastname"=>$lastname,"email"=>$email,"username"=>$username,
			"account_status"=>Will_Core::ACCOUNT_STATUS_NOMINAL];
		$query->execute($data);

		if ($query->rowCount()!=1)
		{
			return new Will_Core_Return(['result'=>"error"]);
		}
		else
		{
			return $this->update_password($username, $password);
		}	
	}
	
	
	function dumpData()
	{
		$result=$this->DB()->query("SELECT * from users");		
		foreach ($result as $row)
		{
			print_r($row);
		}
	}
}
?>

