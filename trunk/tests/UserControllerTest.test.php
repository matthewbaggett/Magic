<?php
require_once(ROOT . '/lib/simpletest/autorun.php');
class UserControllerTest extends UnitTestCase {


   private $test_email_domain = "test.turbocrms.com";
   private $test_user;

   private function deleteTestUsers(){
	   $set = UserSearcher::Factory()
              ->search_by_email("%@{$this->test_email_domain}",UserSearcher::MODE_LIKE)
              ->execute();
      foreach($set as $item){
         $item->delete();
      }
   }
   function setUp() {
	  $this->deleteTestUsers();
      $this->test_good_username        = "jdoe";
      $this->test_good_password        = "I'magoodboy";
      $this->test_banned_username      = "bsimpson";
      $this->test_banned_password      = "B*astards";
      $this->test_inactive_username    = "jqpublic";
      $this->test_inactive_password    = "...";

      $this->test_user = User::Factory()
              ->set_username($this->test_good_username)
              ->set_password(hash("SHA1", $this->test_good_password))
              ->set_firstname("John")
              ->set_surname("Doe")
              ->set_email($this->test_good_username . '@' . $this->test_email_domain)
              ->set_active(User::ACTIVE_ACTIVE)
              ->save();

      $this->test_inactive_user = User::Factory()
              ->set_username($this->test_inactive_username)
              ->set_password(hash("SHA1", $this->test_inactive_password))
              ->set_firstname("Bart")
              ->set_surname("Simpson")
              ->set_email($this->test_inactive_username . '@' . $this->test_email_domain)
              ->set_active(User::ACTIVE_UNACTIVE)
              ->save();

      $this->test_banned_user = User::Factory()
              ->set_username($this->test_banned_username)
              ->set_password(hash("SHA1", $this->test_banned_password))
              ->set_firstname("Bart")
              ->set_surname("Simpson")
              ->set_email($this->test_banned_username . '@' . $this->test_email_domain)
              ->set_active(User::ACTIVE_BANNED)
              ->save();
   }

   function tearDown() {
      $this->deleteTestUsers();
   }

   function testCreation() {
      //Test the good user
      $this->assertNull(UserController::login($this->test_good_username,"wrong_password"),"UserController should throw an Exception when login() is called with bad details");
      $this->assertIsA($user = UserController::login($this->test_good_username,$this->test_good_password),"User","Check that UserController returns a User when you log in.");
      $this->assertEqual($user->get_username(),$this->test_good_username,"Resulting user from login() should be the correct guy");

      //Test the banned user
      $this->assertNull(UserController::login($this->test_banned_username,$this->test_banned_password),"Check that UserController rejects a banned User");

      //Test the inactive user
      $this->assertNull(UserController::login($this->test_inactive_username,$this->test_inactive_password),"Check that UserController rejects a inactive User");
   }
}