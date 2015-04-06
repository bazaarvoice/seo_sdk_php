<?php

require_once 'bvseosdk.php';
require_once 'test/config.php';

/**
 * Test class to test Stories.
 */
class StoryTest extends PHPUnit_Framework_testCase
{
    public $cloud_key = 'myshco-3e3001e88d9c32d19a17cafacb81bec7';
    public $deployment_zone_id = '9344';
    public $product_id = '12345';

    /**
     * Test stories.
     */
    public function testStory()
    {
        // to force is_bot mode
        $_SERVER['HTTP_USER_AGENT'] = "google";

        $bv = new BV(array(
            'bv_root_folder' => $this->deployment_zone_id,
            'subject_id' => $this->product_id,
            'cloud_key' => $this->cloud_key,
            'execution_timeout_bot' => 5000,
            'staging' => TRUE,
        ));

        $content = $bv->stories->getContent();

        $this->assertNotNull($content, "There should be content to proceed further assertion!!");
        $this->assertNotContains("Error - ", $content, "There should be valid content");
        $this->assertNotContains("HTTP status code of", $content, "There should be valid content");
    }

}