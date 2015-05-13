<?php

require_once 'bvseosdk.php';
require_once 'test/config.php';

/**
 * Test Stories class;
 */
class StoriesTest extends PHPUnit_Framework_testCase
{
  var $params = array(
    'bv_root_folder' => 'test',
    'subject_id' => 'test',
    'cloud_key' => 'test',
    'staging' => FALSE,
    'testing' => FALSE,
    'crawler_agent_pattern' => 'msnbot|google|teoma|bingbot|yandexbot|yahoo',
    'ssl_enabled' => FALSE,
    'content_type' => 'product',
    'subject_type' => 'category',
    'execution_timeout' => 500,
    'execution_timeout_bot' => 2000,
    'local_seo_file_root' => '/load/seo/files/locally',
    'load_seo_files_locally' => TRUE,
    'seo_sdk_enabled' => TRUE,
    'proxy_host' => '',
    'proxy_port' => '',
    'charset' => 'UTF-8',
    'base_url' => '/base/url',
    'page_url' => '/page/url&debug=true',
    'include_display_integration_code' => TRUE,
  );

  public function testGetContent()
  {
    $_SERVER['HTTP_USER_AGENT'] = "google";

    $obj = new Stories($this->params);
    $res = $obj->getContent();

    $script_line = '<script>
           $BV.ui("su", "show_stories", {
             productId: "test"
           });
         </script>';
    $this->assertContains($script_line, $res);

    $this->params['include_display_integration_code'] = FALSE;
    $obj = new Stories($this->params);
    $res = $obj->getContent();

    $script_line = '<script>
           $BV.ui("su", "show_stories", {
             productId: "test"
           });
         </script>';
    $this->assertNotContains($script_line, $res);
  }

}
