<?php

require_once 'bvseosdk.php';
require_once 'test/config.php';

/**
 * Test Base class;
 */
class BaseTest extends PHPUnit_Framework_testCase
{
    // After implementing SEO-427 (PHP SDK Refactoring and Consistent Variable Naming)
    // unit tests will be switched to $params_new
    var $params_old = array(
        'deployment_zone_id' => 'test',
        //'bv_root_folder' => 'test',
        //'subject_id' => 'test',
        'product_id' => 'test',
        'cloud_key' => 'test',
        'staging' => FALSE,
        'testing' => FALSE,
        'bot_list' => 'msnbot|google|teoma|bingbot|yandexbot|yahoo',
        //'crawler_agent_pattern' => 'msnbot|google|teoma|bingbot|yandexbot|yahoo',
        'ssl_enabled' => FALSE,
        'bv_product' => 'reviews',
        //'content_type'=> 'product',
        'subject_type' => 'category',
        'execution_timeout' => 500,
        'execution_timeout_bot' => 2000,
        'internal_file_path' => '',
        //'local_seo_file_root' => TRUE,
        //'load_seo_files_locally' => '/load/seo/files/locally',
        'seo_sdk_enabled' => TRUE,
        'proxy_host' => '',
        'proxy_port' => '',
        'charset' => 'UTF-8',
        'base_page_url' => '/base/url',
        //'base_url' => '/base/url',
        'current_page_url' => '/page/url&debug=true'
            //'page_url' => '/page/url&debug=true'
            //'include_display_integration_code' => FALSE,
    );
    var $params_new = array(
        //'deployment_zone_id' => 'test',
        'bv_root_folder' => 'test',
        'subject_id' => 'test',
        //'product_id' => 'test',
        'cloud_key' => 'test',
        'staging' => FALSE,
        'testing' => FALSE,
        //'bot_list' => 'msnbot|google|teoma|bingbot|yandexbot|yahoo',
        'crawler_agent_pattern' => 'msnbot|google|teoma|bingbot|yandexbot|yahoo',
        'ssl_enabled' => FALSE,
        //'bv_product'=> 'product',
        'content_type' => 'reviews',
        'subject_type' => 'category',
        'execution_timeout' => 500,
        'execution_timeout_bot' => 2000,
        //'internal_file_path' => '',
        'local_seo_file_root' => TRUE,
        'load_seo_files_locally' => '/load/seo/files/locally',
        'seo_sdk_enabled' => TRUE,
        'proxy_host' => '',
        'proxy_port' => '',
        'charset' => 'UTF-8',
        'base_url' => '/base/url',
        'page_url' => '/page/url&debug=true'
            //'include_display_integration_code' => FALSE,
    );

    protected static function getMethod($obj, $name)
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function test_buildComment()
    {
        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
                'This test will be compleated after the implementing footer with new naming.'
        );

        $_SERVER['HTTP_USER_AGENT'] = "google";
        $_GET['bvreveal'] = 'debug';
        $this->params_new['page'] = 5;
        $obj = new Base($this->params_new);
        $buildComment = self::getMethod($obj, '_buildComment');
        $res = $buildComment->invokeArgs($obj, array("getContent"));

        //echo $res;
        $this->assertContains('<li data-bvseo="staging">FALSE</li>', $res);
        $this->assertContains('<li data-bvseo="testing">FALSE</li>', $res);
        $this->assertContains('<li data-bvseo="seo.sdk.enabled">TRUE</li>', $res);
        $this->assertContains('<li data-bvseo="seo.sdk.ssl.enabled">FALSE</li>', $res);
        $this->assertContains('<li data-bvseo="proxyHost">none</li>', $res);
        $this->assertContains('<li data-bvseo="proxyPort">0</li>', $res);
        $this->assertContains('<li data-bvseo="seo.sdk.charset">UTF-8</li>', $res);
        $this->assertContains('<li data-bvseo="en">TRUE</li>', $res);
        $this->assertContains('<li data-bvseo="pn">bvseo-5</li>', $res);
        $this->assertContains('<li data-bvseo="userAgent">google</li>', $res);
        $this->assertContains('<li data-bvseo="pageURI">/page/url&debug=true</li>', $res);
        $this->assertContains('<li data-bvseo="baseURI">/base/url</li>', $res);
    }

    public function test_buildSeoUrl()
    {
        $_SERVER['HTTP_USER_AGENT'] = "google";
        $page_number = 5;

        $this->params_old['testing'] = FALSE;
        $this->params_old['staging'] = TRUE;
        $obj = new Base($this->params_old);
        $buildSeoUrl = self::getMethod($obj, '_buildSeoUrl');
        $res = $buildSeoUrl->invokeArgs($obj, array($page_number));

        $this->assertContains('http://seo-stg.bazaarvoice.com/test/test/reviews/category/5/test.htm', $res);

        $this->params_old['testing'] = FALSE;
        $this->params_old['staging'] = FALSE;
        $obj = new Base($this->params_old);
        $buildSeoUrl = self::getMethod($obj, '_buildSeoUrl');
        $res = $buildSeoUrl->invokeArgs($obj, array($page_number));

        $this->assertContains('http://seo.bazaarvoice.com/test/test/reviews/category/5/test.htm', $res);

        $this->params_old['testing'] = TRUE;
        $this->params_old['staging'] = TRUE;
        $obj = new Base($this->params_old);
        $buildSeoUrl = self::getMethod($obj, '_buildSeoUrl');
        $res = $buildSeoUrl->invokeArgs($obj, array($page_number));

        $this->assertContains('http://seo-qa-stg.bazaarvoice.com/test/test/reviews/category/5/test.htm', $res);
        $this->params_old['testing'] = TRUE;
        $this->params_old['staging'] = FALSE;
        $obj = new Base($this->params_old);
        $buildSeoUrl = self::getMethod($obj, '_buildSeoUrl');
        $res = $buildSeoUrl->invokeArgs($obj, array($page_number));

        $this->assertContains('http://seo-qa.bazaarvoice.com/test/test/reviews/category/5/test.htm', $res);

        $this->params_old['testing'] = FALSE;
        $this->params_old['staging'] = FALSE;
        $this->params_old['ssl_enabled'] = FALSE;
        $obj = new Base($this->params_old);
        $buildSeoUrl = self::getMethod($obj, '_buildSeoUrl');
        $res = $buildSeoUrl->invokeArgs($obj, array($page_number));

        $this->assertContains('http://seo.bazaarvoice.com/test/test/reviews/category/5/test.htm', $res);

        $this->params_old['ssl_enabled'] = TRUE;
        $obj = new Base($this->params_old);
        $buildSeoUrl = self::getMethod($obj, '_buildSeoUrl');
        $res = $buildSeoUrl->invokeArgs($obj, array($page_number));

        $this->assertContains('https://seo.bazaarvoice.com/test/test/reviews/category/5/test.htm', $res);

        $this->params_old['ssl_enabled'] = FALSE;
        $this->params_old['content_sub_type'] = "stories";
        $obj = new Base($this->params_old);
        $buildSeoUrl = self::getMethod($obj, '_buildSeoUrl');
        $res = $buildSeoUrl->invokeArgs($obj, array($page_number));

        $this->assertContains('http://seo.bazaarvoice.com/test/test/reviews/category/5/stories/test.htm', $res);

        $this->params_old['content_sub_type'] = "storiesgrid";
        $obj = new Base($this->params_old);
        $buildSeoUrl = self::getMethod($obj, '_buildSeoUrl');
        $res = $buildSeoUrl->invokeArgs($obj, array($page_number));

        $this->assertContains('http://seo.bazaarvoice.com/test/test/reviews/category/5/storiesgrid/test.htm', $res);

        unset($this->params_old['content_sub_type']);
        $this->params_old['internal_file_path'] = "/var/www/html/";
        $obj = new Base($this->params_old);
        $buildSeoUrl = self::getMethod($obj, '_buildSeoUrl');
        $res = $buildSeoUrl->invokeArgs($obj, array($page_number));

        $this->assertContains('/var/www/html/test/reviews/category/5/test.htm', $res);
    }

    public function test_charsetEncode()
    {
        $_SERVER['HTTP_USER_AGENT'] = "google";
        $this->params_old['charset'] = 'Windows-1251';

        $obj = new Base($this->params_old);
        $charsetEncode = self::getMethod($obj, '_charsetEncode');

        $res = $charsetEncode->invokeArgs($obj, array("This is the Euro symbol 'в‚¬'"));
        $this->assertEquals("This is the Euro symbol '€'", $res);

        $res = $charsetEncode->invokeArgs($obj, array("РљРёСЂРёР»Р»РёС†Р°"));
        $this->assertEquals("Кириллица", $res);
    }

    public function test_checkCharset()
    {
        $_SERVER['HTTP_USER_AGENT'] = "google";
        $this->params_old['charset'] = 'NOT_EXISTING_CHARSET';
        $obj = new Base($this->params_old);
        $checkCharset = self::getMethod($obj, '_checkCharset');

        $res = $checkCharset->invokeArgs($obj, array("Lorem ipsum dolor sit amet"));

        //should be set UTF-8 as default charset
        $this->assertEquals("UTF-8", $obj->config['charset']);



        $this->params_old['charset'] = 'SJIS';
        $obj = new Base($this->params_old);
        $checkCharset = self::getMethod($obj, '_checkCharset');

        $res = $checkCharset->invokeArgs($obj, array("Lorem ipsum dolor sit amet"));

        //should be set SJIS as predefined
        $this->assertEquals("SJIS", $obj->config['charset']);
    }

    public function test_fetchCloudContent()
    {
        $_SERVER['HTTP_USER_AGENT'] = "google";
        $this->params_old['internal_file_path'] = '';
        $obj = $this->getMockBuilder('Base')
                ->setConstructorArgs(array($this->params_old))
                ->setMethods(['curlError', 'curlErrorNo', 'curlExecute', 'curlInfo'])
                ->getMock();
        $obj->expects($this->any())
                ->method('curlError')
                ->will($this->returnValue('No errors'));
        $obj->expects($this->any())
                ->method('curlErrorNo')
                ->will($this->returnValue(0));
        $obj->expects($this->any())
                ->method('curlExecute')
                ->will($this->returnValue('<div id="BVRRContainer">Mock content for unit tests.</div>'));
        $obj->expects($this->any())
                ->method('curlInfo')
                ->will($this->returnValue(array('http_code' => 403, 'total_time' => 100)));


        $path = 'test/data/universalSEO.html';
        $res = '';
        $fetchCloudContent = self::getMethod($obj, '_fetchCloudContent');
        $res = $fetchCloudContent->invokeArgs($obj, array($path));
        $this->assertEmpty($res);
        //$this->assertContains("HTTP status code of 403 was returned", $obj->getBVMessages());

        $obj->expects($this->any())
                ->method('curlInfo')
                ->will($this->returnValue(array('http_code' => 200, 'total_time' => 100)));


        $obj = $this->getMockBuilder('Base')
                ->setConstructorArgs(array($this->params_old))
                ->setMethods(['curlError', 'curlErrorNo', 'curlExecute', 'curlInfo'])
                ->getMock();
        $obj->expects($this->any())
                ->method('curlError')
                ->will($this->returnValue('No errors'));
        $obj->expects($this->any())
                ->method('curlErrorNo')
                ->will($this->returnValue(0));
        $obj->expects($this->any())
                ->method('curlExecute')
                ->will($this->returnValue('<div id="BVRRContainer">Mock content for unit tests.</div>'));
        $obj->expects($this->any())
                ->method('curlInfo')
                ->will($this->returnValue(array('http_code' => 200, 'total_time' => 100)));

        $res = '';
        $fetchCloudContent = self::getMethod($obj, '_fetchCloudContent');
        $res = $fetchCloudContent->invokeArgs($obj, array($path));
        $this->assertNotEmpty($res);
        $this->assertContains("Mock content for unit tests", $res);
    }

    public function test_fetchFileContent()
    {
        $_SERVER['HTTP_USER_AGENT'] = "google";
        $path = 'test/data/universalSEO.html';

        $obj = new Base($this->params_old);
        $fetchFileContent = self::getMethod($obj, '_fetchFileContent');

        $res = $fetchFileContent->invokeArgs($obj, array($path));

        $this->assertContains("Content for unit tests", $res);

        // Need @file_get_contents instead file_get_contents to work.
        $path = 'unexisting/test/path/universalSEO.html';

        $obj = new Base($this->params_old);
        $fetchFileContent = self::getMethod($obj, '_fetchFileContent');

        $res = $fetchFileContent->invokeArgs($obj, array($path));

        $this->assertFalse($res);
        $this->assertContains("Local file content was uploaded", $obj->getBVMessages());
    }

    public function test_fetchSeoContent()
    {
        $_SERVER['HTTP_USER_AGENT'] = "google";
        $this->params_old['internal_file_path'] = '';
        $obj = $this->getMockBuilder('Base')
                ->setConstructorArgs(array($this->params_old))
                ->setMethods(['curlError', 'curlErrorNo', 'curlExecute', 'curlInfo'])
                ->getMock();
        $obj->expects($this->any())
                ->method('curlError')
                ->will($this->returnValue('No errors'));
        $obj->expects($this->any())
                ->method('curlErrorNo')
                ->will($this->returnValue(0));
        $obj->expects($this->any())
                ->method('curlExecute')
                ->will($this->returnValue('<div id="BVRRContainer">Mock content for unit tests.</div>'));
        $obj->expects($this->any())
                ->method('curlInfo')
                ->will($this->returnValue(array('http_code' => 200, 'total_time' => 100)));


        $path = 'test/data/universalSEO.html';
        $fetchSeoContent = self::getMethod($obj, '_fetchSeoContent');
        $res = $fetchSeoContent->invokeArgs($obj, array($path));

        $this->assertContains("Mock content for unit tests.", $res);

        $this->params_old['internal_file_path'] = 'www';
        $obj = new Base($this->params_old);
        $fetchSeoContent = self::getMethod($obj, '_fetchSeoContent');
        $res = $fetchSeoContent->invokeArgs($obj, array($path));

        $this->assertContains("Content for unit tests.", $res);
    }

    public function test_getFullSeoContents()
    {
        $_SERVER['HTTP_USER_AGENT'] = "google";
        $this->params_old['seo_sdk_enabled'] = TRUE;
        $obj = $this->getMockBuilder('Base')
                ->setConstructorArgs(array($this->params_old))
                ->setMethods(['curlError', 'curlErrorNo', 'curlExecute', 'curlInfo'])
                ->getMock();
        $obj->expects($this->any())
                ->method('curlError')
                ->will($this->returnValue('No errors'));
        $obj->expects($this->any())
                ->method('curlErrorNo')
                ->will($this->returnValue(0));
        $obj->expects($this->any())
                ->method('curlExecute')
                ->will($this->returnValue('<div id="BVRRContainer">Mock content for unit tests.</div>'));
        $obj->expects($this->any())
                ->method('curlInfo')
                ->will($this->returnValue(array('http_code' => 200, 'total_time' => 100)));


        $getFullSeoContents = self::getMethod($obj, '_getFullSeoContents');

        $res = $getFullSeoContents->invokeArgs($obj, array("getContent"));

        $this->assertNotEmpty($res);
        $this->assertContains("Mock content for unit tests", $res);


        $this->params_old['seo_sdk_enabled'] = FALSE;
        $obj = new Base($this->params_old);
        $getFullSeoContents = self::getMethod($obj, '_getFullSeoContents');
        $res = $getFullSeoContents->invokeArgs($obj, array("getContent"));
        $this->assertEmpty($res);
        $this->assertContains("SEO SDK is disabled", $obj->getBVMessages());
    }

    public function test_getPageNumber()
    {
        $_SERVER['HTTP_USER_AGENT'] = "google";
        $obj = new Base($this->params_old);
        $getPageNumber = self::getMethod($obj, '_getPageNumber');
        $res = $getPageNumber->invokeArgs($obj, array());
        $this->assertEquals(1, $res);

        $_GET['bvpage'] = 2;
        $_GET['bvrrp'] = 'Main_Site-en_US/reviews/product/2/00636.htm';
        $this->params_old['base_page_url'] = 'http://localhost/Example.php/index.php?bvrrp=Main_Site-en_US/reviews/product/2/00636.htm ';
        $obj = new Base($this->params_old);
        $getPageNumber = self::getMethod($obj, '_getPageNumber');
        $res = $getPageNumber->invokeArgs($obj, array());
        $this->assertEquals(2, $res);
        $this->assertNotContains($_GET['bvrrp'], $obj->config['base_page_url']);

        unset($_GET['bvpage']);
        $_GET['bvrrp'] = 'Main_Site-en_US/reviews/product/3/00636.htm';
        $this->params_old['base_page_url'] = 'http://localhost/Example.php/index.php?bvrrp=Main_Site-en_US/reviews/product/2/00636.htm ';
        $obj = new Base($this->params_old);
        $getPageNumber = self::getMethod($obj, '_getPageNumber');
        $res = $getPageNumber->invokeArgs($obj, array());
        $this->assertEquals(3, $res);
        $this->assertNotContains($_GET['bvrrp'], $obj->config['base_page_url']);


        //bvstate test, ucoment after implementing
        //unset($_GET['bvpage']);
        //unset($_GET['bvrrp']);
        //$this->params_old['page_url'] = 'http://localhost/Example.php/index.php?bvpage=pg4/ctr/std/id87645&bvstate=pg:3/ct:r';
        //$obj = new Base($this->params_old);
        //$getPageNumber = self::getMethod($obj, '_getPageNumber');
        //$res = $getPageNumber->invokeArgs($obj, array());
        //$this->assertEquals(3, $res);
    }

    public function test_isBot()
    {
        $_GET['bvreveal'] = "something";
        $obj = new Base($this->params_old);
        $isBot = self::getMethod($obj, '_isBot');
        $res = $isBot->invokeArgs($obj, array());
        $this->assertTrue($res);

        $_SERVER['HTTP_USER_AGENT'] = "NON_EXISTING";
        unset($_GET['bvreveal']);
        //should be removed
        $this->params_old['bot_detection'] = TRUE;
        $obj = new Base($this->params_old);
        $isBot = self::getMethod($obj, '_isBot');
        $res = $isBot->invokeArgs($obj, array());
        $res = !empty($res);
        $this->assertFalse($res);

        $_SERVER['HTTP_USER_AGENT'] = "google";
        unset($_GET['bvreveal']);
        //should be removed
        $this->params_old['bot_detection'] = TRUE;
        $obj = new Base($this->params_old);
        $isBot = self::getMethod($obj, '_isBot');
        $res = $isBot->invokeArgs($obj, array());
        $res = !empty($res);
        $this->assertTrue($res);
    }

    public function test_isSdkEnabled()
    {
        $_SERVER['HTTP_USER_AGENT'] = "google";
        $this->params_old['seo_sdk_enabled'] = TRUE;
        $obj = new Base($this->params_old);
        $isSdkEnabled = self::getMethod($obj, '_isSdkEnabled');
        $res = $isSdkEnabled->invokeArgs($obj, array());
        $this->assertTrue($res);

        $this->params_old['seo_sdk_enabled'] = FALSE;
        $_GET['bvreveal'] = "debug";
        $obj = new Base($this->params_old);
        $isSdkEnabled = self::getMethod($obj, '_isSdkEnabled');
        $res = $isSdkEnabled->invokeArgs($obj, array());
        $this->assertTrue($res);

        $this->params_old['seo_sdk_enabled'] = FALSE;
        $_GET['bvreveal'] = "not_debug";
        $obj = new Base($this->params_old);
        $isSdkEnabled = self::getMethod($obj, '_isSdkEnabled');
        $res = $isSdkEnabled->invokeArgs($obj, array());
        $this->assertFalse($res);
    }

    public function test_renderAggregateRating()
    {
        $this->params_old['bot_detection'] = TRUE;
        $_SERVER['HTTP_USER_AGENT'] = "google";

        // remove this 2 lines when SEO-427 will be done
        $_GET['bvreveal'] = "not_debug";
        $this->params_old['content_type'] = "product";

        $obj = $this->getMockBuilder('Base')
                ->setConstructorArgs(array($this->params_old))
                ->setMethods(['curlError', 'curlErrorNo', 'curlExecute', 'curlInfo'])
                ->getMock();
        $obj->expects($this->any())
                ->method('curlError')
                ->will($this->returnValue('No errors'));
        $obj->expects($this->any())
                ->method('curlErrorNo')
                ->will($this->returnValue(0));
        $obj->expects($this->any())
                ->method('curlExecute')
                ->will($this->returnValue('<div id="BVRRContainer">
						<!--begin-aggregate-rating--> Mock aggeagate rating	<!--end-aggregate-rating-->
						<!--begin-reviews--> Mock reviews <!--end-reviews-->
						<!--begin-pagination--> Mock pagination	<!--end-pagination-->
						<p>Mock content for unit tests.</p>
					</div>'));
        $obj->expects($this->any())
                ->method('curlInfo')
                ->will($this->returnValue(array('http_code' => 200, 'total_time' => 100)));


        $renderAggregateRating = self::getMethod($obj, '_renderAggregateRating');

        $res = $renderAggregateRating->invokeArgs($obj, array());

        $this->assertNotEmpty($res);
        $this->assertContains("Mock aggeagate rating", $res);
        $this->assertNotContains("Mock reviews", $res);
        $this->assertNotContains("Mock pagination", $res);
    }

    public function test_renderReviews()
    {
        $this->params_old['bot_detection'] = TRUE;
        $_SERVER['HTTP_USER_AGENT'] = "google";

        // remove this 2 lines when SEO-427 will be done
        $_GET['bvreveal'] = "not_debug";
        $this->params_old['content_type'] = "product";

        $obj = $this->getMockBuilder('Base')
                ->setConstructorArgs(array($this->params_old))
                ->setMethods(['curlError', 'curlErrorNo', 'curlExecute', 'curlInfo'])
                ->getMock();
        $obj->expects($this->any())
                ->method('curlError')
                ->will($this->returnValue('No errors'));
        $obj->expects($this->any())
                ->method('curlErrorNo')
                ->will($this->returnValue(0));
        $obj->expects($this->any())
                ->method('curlExecute')
                ->will($this->returnValue('<div id="BVRRContainer">
						<!--begin-aggregate-rating--> Mock aggeagate rating	<!--end-aggregate-rating-->
						<!--begin-reviews--> Mock reviews <!--end-reviews-->
						<!--begin-pagination--> Mock pagination	<!--end-pagination-->
						<p>Mock content for unit tests.</p>
					</div>'));
        $obj->expects($this->any())
                ->method('curlInfo')
                ->will($this->returnValue(array('http_code' => 200, 'total_time' => 100)));


        $renderReviews = self::getMethod($obj, '_renderReviews');

        $res = $renderReviews->invokeArgs($obj, array());

        $this->assertNotEmpty($res);
        $this->assertNotContains("Mock aggeagate rating", $res);
        $this->assertContains("Mock reviews", $res);
        $this->assertContains("Mock pagination", $res);
    }

    public function test_renderSEO()
    {
        // All bot_detection should be removed after implementing SEO-430
        $this->params_old['bot_detection'] = TRUE;
        // remove this line when SEO-427 will be done
        $this->params_old['content_type'] = "product";
        //is bot
        $_SERVER['HTTP_USER_AGENT'] = "google";

        $obj = $this->getMockBuilder('Base')
                ->setConstructorArgs(array($this->params_old))
                ->setMethods(['curlError', 'curlErrorNo', 'curlExecute', 'curlInfo'])
                ->getMock();
        $obj->expects($this->any())
                ->method('curlError')
                ->will($this->returnValue('No errors'));
        $obj->expects($this->any())
                ->method('curlErrorNo')
                ->will($this->returnValue(0));
        $obj->expects($this->any())
                ->method('curlExecute')
                ->will($this->returnValue('<div id="BVRRContainer">
						<p>Mock content for unit tests.</p>
					</div>'));
        $obj->expects($this->any())
                ->method('curlInfo')
                ->will($this->returnValue(array('http_code' => 200, 'total_time' => 100)));


        $renderSEO = self::getMethod($obj, '_renderSEO');

        $res = $renderSEO->invokeArgs($obj, array('getContent'));

        $this->assertNotEmpty($res);
        $this->assertContains("Mock content for unit tests", $res);

        //is not bot
        $_SERVER['HTTP_USER_AGENT'] = "NOT_BOT";
        $this->params_old['execution_timeout'] = 0;
        $obj = $this->getMockBuilder('Base')
                ->setConstructorArgs(array($this->params_old))
                ->setMethods(['curlError', 'curlErrorNo', 'curlExecute', 'curlInfo'])
                ->getMock();
        $obj->expects($this->any())
                ->method('curlError')
                ->will($this->returnValue('No errors'));
        $obj->expects($this->any())
                ->method('curlErrorNo')
                ->will($this->returnValue(0));
        $obj->expects($this->any())
                ->method('curlExecute')
                ->will($this->returnValue('<div id="BVRRContainer">
						<p>Mock content for unit tests.</p>
					</div>'));
        $obj->expects($this->any())
                ->method('curlInfo')
                ->will($this->returnValue(array('http_code' => 200, 'total_time' => 100)));


        $renderSEO = self::getMethod($obj, '_renderSEO');

        $res = $renderSEO->invokeArgs($obj, array('getContent'));

        $this->assertNotEmpty($res);
        $this->assertContains("JavaScript-only Display", $res);
    }

    public function test_replaceSection()
    {
        $_SERVER['HTTP_USER_AGENT'] = "google";
        $obj = new Base($this->params_old);
        $replaceSection = self::getMethod($obj, '_replaceSection');
        $str = '<div id="BVRRContainer"><!--begin-reviews--> Mock reviews <!--end-reviews--></div>';
        $search_str_begin = '<!--begin-reviews-->';
        $search_str_end = '<!--end-reviews-->';
        $res = $replaceSection->invokeArgs($obj, array($str, $search_str_begin, $search_str_end));

        $this->assertNotContains("<!--begin-reviews--> Mock reviews <!--end-reviews-->", $res);
        $this->assertEquals('<div id="BVRRContainer"></div>', $res);
    }

    public function test_replaceTokens()
    {
        $_SERVER['HTTP_USER_AGENT'] = "google";
        $str = 'Base url: {INSERT_PAGE_URI}';
        $base_url = 'www.base.url';
        $this->params_old['base_page_url'] = $base_url;
        //$this->params_new['base_url'] = $base_url;
        $obj = new Base($this->params_old);
        $replaceTokens = self::getMethod($obj, '_replaceTokens');

        $res = $replaceTokens->invokeArgs($obj, array($str));

        $this->assertEquals('Base url: www.base.url?', $res);

        $base_url = 'www.base.url?debug=true';
        $this->params_old['base_page_url'] = $base_url;
        //$this->params_new['base_url'] = $base_url;
        $obj = new Base($this->params_old);
        $replaceTokens = self::getMethod($obj, '_replaceTokens');

        $res = $replaceTokens->invokeArgs($obj, array($str));

        $this->assertEquals('Base url: www.base.url?debug=true&', $res);
    }

    public function test_setBuildMessage()
    {
        $_SERVER['HTTP_USER_AGENT'] = "google";
        $msg1 = 'The message1;';
        $msg2 = 'The message2';
        $obj = new Base($this->params_old);
        $setBuildMessage = self::getMethod($obj, '_setBuildMessage');

        $setBuildMessage->invokeArgs($obj, array($msg1));
        $setBuildMessage->invokeArgs($obj, array($msg2));
        $res = $obj->getBVMessages();

        $this->assertEquals(' The message1; The message2;', $res);
    }

}
