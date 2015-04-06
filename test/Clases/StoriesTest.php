<?php

require_once 'bvseosdk.php';
require_once 'test/config.php';

/**
 * Test Stories class;
 */
class StoriesTest extends PHPUnit_Framework_testCase
{
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
        'bv_product' => 'product',
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
        'current_page_url' => '/page/url&debug=true',
         //'page_url' => '/page/url&debug=true'
         'include_display_integration_code' => TRUE,
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
        'content_type' => 'product',
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

	public function testGetContent()
	{
        $_SERVER['HTTP_USER_AGENT'] = "google";
	 	//this 2 lines should be removed
        $this->params_old['bot_detection'] = TRUE;
		$this->params_old['content_type'] = "product";

	    $obj = new Stories($this->params_old);
		$res = $obj->getContent();
		//echo $res;
		$script_line = '<script>
                   $BV.ui("su", "show_stories", {
                       productId: "test"
                   });
               </script>';
		$this->assertContains($script_line, $res);

        $this->params_old['include_display_integration_code'] = FALSE;
	    $obj = new Stories($this->params_old);
		$res = $obj->getContent();
		//echo $res;
		$script_line = '<script>
                   $BV.ui("su", "show_stories", {
                       productId: "test"
                   });
               </script>';
        $this->assertNotContains($script_line, $res);
	}
}
