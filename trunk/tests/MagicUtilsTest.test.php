<?php
require_once(ROOT . '/lib/simpletest/autorun.php');
class MagicUtilsTest extends UnitTestCase {

    function setUp() {
         
    }

    function tearDown() {
    }

    function testCreation() {
         $this->assertIsA(MagicQuery::Factory(),"MagicQuery");

    }
}