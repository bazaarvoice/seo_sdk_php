<?php

require_once 'bvseosdk.php';
require_once 'test/config.php';

/**
 * Test class for testing charset stting
 */
class CharsetTest extends PHPUnit_Framework_testCase
{
    var $params = array(
        'deployment_zone_id' => 'test',
        'product_id' => 'test',
        'cloud_key' => 'test',
        'staging' => TRUE,
    );

    // Use reflection to test private methods
    protected static function getMethod($name)
    {
        $class = new ReflectionClass('Base');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * Test charset.
     */
    public function testCharsetEncode()
    {

        $params['charset'] = 'Windows-1251';

        $charsetEncode = self::getMethod('_charsetEncode');

        $obj = new Base($params);
        $a = $charsetEncode->invokeArgs($obj, array("This is the Euro symbol 'в‚¬'"));
        $this->assertEquals("This is the Euro symbol '€'", $a);

        $b = $charsetEncode->invokeArgs($obj, array("РљРёСЂРёР»Р»РёС†Р°"));
        $this->assertEquals("Кириллица", $b);
    }

    public function testCharsetCheck()
    {

        $params['charset'] = 'NOT_EXISTING_CHARSET';

        $checkCharset = self::getMethod('_checkCharset');

        // Check for set to default
        $obj = new Base($params);
        $checkCharset->invokeArgs($obj, array("Hello world!"));
        $this->assertEquals("UTF-8", $obj->config['charset']);

        // Check correct charset
        $params['charset'] = 'UTF-16';
        $obj = new Base($params);
        $checkCharset->invokeArgs($obj, array("Hello world!"));
        $this->assertEquals("UTF-16", $obj->config['charset']);
    }

}

