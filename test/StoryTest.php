<?php

require_once 'bvseosdk.php';
require_once 'test/config.php';

define('CLOUD_KEY', 'myshco-3e3001e88d9c32d19a17cafacb81bec7');
define('DEPLOYMENT_ZONE_ID', '9344');
define('PRODUCT_ID', '12345');

class StoryTest extends PHPUnit_Framework_testCase {

	/**
	 * Test stories.
	 */
	public function testStory () {
		// to force is_bot mode
		$_SERVER['HTTP_USER_AGENT'] = "google";

		$bv = new BV(array(
			'deployment_zone_id' => DEPLOYMENT_ZONE_ID,
			'product_id' => PRODUCT_ID,
			'cloud_key' => CLOUD_KEY,
			'staging' => TRUE,
		));

		$content = $bv->stories->getContent();

		$this->assertNotNull($content, "There should be content to proceed further assertion!!");
		$this->assertNotContains("Error - ", $content, "There should be valid content");
		$this->assertNotContains("HTTP status code of", $content, "There should be valid content");
	}
}
