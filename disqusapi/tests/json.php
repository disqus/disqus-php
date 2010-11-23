<?php

require_once('PHPUnit/Framework.php');
require_once(realpath(dirname(__FILE__).'/../json.php'));

class JSONTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->json = new JSON;
	}
	
	public function test_decoding() {
		$data = '{
		  "a": false,
		  "b": 1
		}';
		$set1 = json_decode($data);
		$set2 = $this->json->unserialize($data);
		$this->assertEquals($set1->id, $set2->id);
		$this->assertEquals($set1, $set2);
	}
}

?>