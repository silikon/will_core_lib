<?php
require_once('simpletest/autorun.php');
require_once('classes/will_core_logging.php');

class TestWillCoreLogging extends UnitTestCase {	
	function testLoggingItemsIn() {
		$username='a_user';
		$ip="1.2.3.4";
		$log=new Will_Core_Logging($username,$ip);

		$row_id=999;
		$ret=$log->get_log_entry($row_id);
		$this->assertTrue(gettype($ret)==='boolean');

		$row_id=$log->log_in();
		$ret=$log->get_log_entry($row_id);
		$this->assertFalse(gettype($ret)==='boolean');
		$this->assertTrue($ret['row_id']===$row_id);

		$row_id=$log->log_in_failure();
		$ret=$log->get_log_entry($row_id);
		$this->assertFalse(gettype($ret)==='boolean');
		$this->assertTrue($ret['row_id']===$row_id);

		$row_id=$log->log_out();
		$ret=$log->get_log_entry($row_id);
		$this->assertFalse(gettype($ret)==='boolean');
		$this->assertTrue($ret['row_id']===$row_id);

		$row_id=$log->change_password();
		$ret=$log->get_log_entry($row_id);
		$this->assertFalse(gettype($ret)==='boolean');
		$this->assertTrue($ret['row_id']===$row_id);

		$row_id=$log->reset_password_request();
		$ret=$log->get_log_entry($row_id);
		$this->assertFalse(gettype($ret)==='boolean');
		$this->assertTrue($ret['row_id']===$row_id);

		$row_id=$log->reset_password();
		$ret=$log->get_log_entry($row_id);
		$this->assertFalse(gettype($ret)==='boolean');
		$this->assertTrue($ret['row_id']===$row_id);

		$row_id=$log->register();
		$ret=$log->get_log_entry($row_id);
		$this->assertFalse(gettype($ret)==='boolean');
		$this->assertTrue($ret['row_id']===$row_id);

		$row_id=$log->block();
		$ret=$log->get_log_entry($row_id);
		$this->assertFalse(gettype($ret)==='boolean');
		$this->assertTrue($ret['row_id']===$row_id);

		$row_id=$log->unblock();
		$ret=$log->get_log_entry($row_id);
		$this->assertFalse(gettype($ret)==='boolean');
		$this->assertTrue($ret['row_id']===$row_id);		
	}
}
?>
