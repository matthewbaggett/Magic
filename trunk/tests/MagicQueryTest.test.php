<?php
require_once(ROOT . '/lib/simpletest/autorun.php');
class MagicQueryTest extends UnitTestCase {

    function setUp() {
         
    }

    function tearDown() {
    }

    function testCreation() {
      $this->assertTrue(MagicUtils::from_camel_case("ThisIsATestString"),"this_is_a_test_string");
       $this->assertTrue(MagicUtils::to_camel_case("this_is_a_test_string"),"ThisIsATestString");
    }
}