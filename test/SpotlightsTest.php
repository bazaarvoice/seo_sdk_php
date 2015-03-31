<?php

require_once 'bvseosdk.php';
require_once 'test/config.php';

/**
 * Test class to test Spotlights.
 */
class SpotlightsTest extends PHPUnit_Framework_testCase
{
    public $cloud_key = 'test';
    public $deployment_zone_id = 'test';
    public $product_id = 'test';

    /**
     * Test spotlights.
     */
    public function testSpotlights()
    {
        $_SERVER['HTTP_USER_AGENT'] = "google";
        $_GET['bvreveal'] = 'debug';

        $bv = new BV(array(
            'deployment_zone_id' => $this->deployment_zone_id,
            'product_id' => $this->product_id,
            'cloud_key' => $this->cloud_key,
        ));

        $content = $bv->spotlights->getContent();
        $this->assertNotNull($content, "There should be content to proceed further assertion!!");
        $this->assertContains('seo.bazaarvoice.com/test/test/spotlights/category/1/test.htm', $content);
        $this->assertContains('getContent', $content);
        

        $content = $bv->spotlights->getAggregateRating();
        $this->assertNotNull($content, "There should be content to proceed further assertion!!");
        $this->assertContains('getAggregateRating', $content);
        

        $content = $bv->spotlights->getReviews();
        $this->assertNotNull($content, "There should be content to proceed further assertion!!");
        $this->assertContains('getReviews', $content);
    }

}