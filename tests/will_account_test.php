<?php
require_once('simpletest/autorun.php');
require_once('classes/will_core_account.php');

class TestWillCoreAccount extends UnitTestCase {	
    function testAddAdmin() {
		$testAc=new Will_Account();
		$testAc->create_tables(true);
		$password="admin";
		$testAc->add_admin_account($password);
		$validate=$testAc->validate_login("admin", $password);
		$this->assertTrue($validate->payload['result']===Will_Core_Return::RETURN_STATUS_OK);
		$validate=$testAc->validate_login("admin", "wrongpassword");
		$this->assertFalse($validate->payload['result']===Will_Core_Return::RETURN_STATUS_OK);
    }
    
    function testAddUserAccount() {
		$testAc=new Will_Account();
		$firstname='fn';
		$lastname='ln';
		$email='testemail';
		$username='342usernameasfd';
		$password='pasYEP';

		$validate=$testAc->validate_login($username, $password);
		$this->assertTrue($validate->payload['result']===Will_Core_Return::RETURN_STATUS_ERROR);

		$ret=$testAc->add_account($firstname, $lastname, $email, $username, $password);

		$validate=$testAc->validate_login($username, $password);
		$this->assertTrue($validate->payload['result']===Will_Core_Return::RETURN_STATUS_OK);
	}

    function testIDRetrievalFunctions() {
		$testAc=new Will_Account();
		$username="342usernameasfd";
		$ret=$testAc->get_id_from_username($username);
		$this->assertTrue($ret->payload['result']===Will_Core_Return::RETURN_STATUS_OK);
		$ret=$testAc->get_username_from_id($ret->payload['user_id']);
		$this->assertTrue($ret->payload['username']===$username);
		$ret=$testAc->get_email_from_username($username);
		$ret=$testAc->get_username_from_email($ret->payload['email']);
		$this->assertTrue($ret->payload['username']===$username);
    }
    
    function testIDsTakenFunctions() {
		$testAc=new Will_Account();
		$username="342usernameasfd";
		$email='testemail';
		
		$ret=$testAc->is_username_taken($username);
		$this->assertTrue($ret->payload['is_taken']);
		$ret=$testAc->is_username_taken("thisonenottaken");
		$this->assertFalse($ret->payload['is_taken']);
		
		$ret=$testAc->is_email_taken('not real');
		$this->assertFalse($ret->payload['is_taken']);
		$ret=$testAc->is_email_taken($email);
		$this->assertTrue($ret->payload['is_taken']);
	}

    function testPasswordChange() {
		$testAc=new Will_Account();
		$password="new_password";
		$username="342usernameasfd";

		$validate=$testAc->validate_login($username, $password);
		$this->assertFalse($validate->payload['result']===Will_Core_Return::RETURN_STATUS_OK);

		$testAc->update_password($username, $password);
		$validate=$testAc->validate_login($username, $password);
		$this->assertTrue($validate->payload['result']===Will_Core_Return::RETURN_STATUS_OK);
	}

    function testPasswordReset() {
		$testAc=new Will_Account();
		$username="342usernameasfd";
		$ret=$testAc->create_reset_password($username);
		$this->assertTrue($ret->payload['result']===Will_Core_Return::RETURN_STATUS_OK);

		$new_password='new12356';
		$ret=$testAc->reset_password($username, $new_password,$ret->payload['reset_password']);
		$this->assertTrue($ret->payload['result']===Will_Core_Return::RETURN_STATUS_OK);

		$validate=$testAc->validate_login($username, $new_password);
		$this->assertTrue($validate->payload['result']===Will_Core_Return::RETURN_STATUS_OK);
	}
	
	function testBlockingUser() {
		$testAc=new Will_Account();
		$password="new_password";
		$username="342usernameasfd";

		$ret=$testAc->get_user_account_status($username);
		$this->assertTrue($ret->payload['account_status']===Will_Core::ACCOUNT_STATUS_NOMINAL);

		$ret=$testAc->block_user($username);
		$ret=$testAc->get_user_account_status($username);
		$this->assertTrue($ret->payload['account_status']===Will_Core::ACCOUNT_STATUS_BLOCKED);

		$ret=$testAc->restrict_user($username);
		$ret=$testAc->get_user_account_status($username);
		$this->assertTrue($ret->payload['account_status']===Will_Core::ACCOUNT_STATUS_RESTRICTED);

		$ret=$testAc->unblock_user($username);
		$ret=$testAc->get_user_account_status($username);
		$this->assertTrue($ret->payload['account_status']===Will_Core::ACCOUNT_STATUS_NOMINAL);
	}
	
	function testRemoveUser() {
		$testAc=new Will_Account();
		$username='342usernameasfd';
		$ret=$testAc->get_account_info($username);
		$this->assertTrue($ret->payload['account_info']['account_status']===Will_Core::ACCOUNT_STATUS_NOMINAL);
		
		$testAc->remove_user($username);
		$ret=$testAc->get_account_info($username);
		$this->assertTrue($ret->payload['result']===Will_Core_Return::RETURN_STATUS_ERROR);
	}
}
?>

