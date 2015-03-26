<?php

/**
 * BV PHP SEO SDK
 *
 * Base code to power either SEO or SEO and display. This SDK
 * is provided as is and Bazaarvoice, Inc. is not responsible
 * for future maintenance or support.  You are free to modify
 * this SDK as needed to suit your needs.
 *
 * This SDK was built with the following assumptions:
 *      - you are running PHP 5 or greater
 *      - you have the curl library installed
 *      - every request has the user agent header
 *        in it (if using a CDN like Akamai additional configuration
 *        maybe required).
 *
 */
/**
 * Example usage:
 *
 * require(bvsdk.php);
 *
 * $bv = new BV(array(
 *    'deployment_zone_id' => '1234-en_US',
 *    'product_id' => 'XXYYY',
 *    'cloud_key' => 'company-cdfa682b84bef44672efed074093ccd3',
 *    'staging' => FALSE
 * ));
 *
 */
require_once 'BVUtility.php';

// Should be declared in file where _execTimer will be used.
// If declared in the another file it does not affect the current file.
declare(ticks = 1);

// Default charset will be used in case charset parameter is not properly configured by user.
define('DEFAULT_CHARSET', 'UTF-8');

// ------------------------------------------------------------------------

/**
 * BV Class
 *
 * When you instantiate the BV class, pass it's constructor an array
 * containing the following key value pairs.
 *
 *   Required fields:
 *      deployment_zone_id (string)
 *      product_id (string)
 *      cloud_key (string)
 *
 *   Optional fields
 *      current_page_url (string) (defaults to detecting the current_page automatically)
 *      staging (boolean) (defaults to false, need to put true for testing with staging data)
 *      subject_type (string) (defaults to product, for questions you can pass in categories here if needed)
 *      content_sub_type (string) (defaults to stories, for stories you can pass either STORIES_LIST or STORIES_GRID content type)
 *      execution_timeout (int) (in milliseconds) (defaults to 500ms, to set period of time before the BVSEO injection times out for user agents that do not match the criteria set in CRAWLER_AGENT_PATTERN)
 *      execution_timeout_bot (int) (in milliseconds) (defaults to 2000ms, to set period of time before the BVSEO injection times out for user agents that match the criteria set in CRAWLER_AGENT_PATTERN)
 *      charset (string) (defaults to UTF-8, to set alternate character for SDK output)
 *      bv_product (string) (defaults to reviews)
 *      bot_list (string) (defaults to msnbot|googlebot|teoma|bingbot|yandexbot|yahoo)
 */
class BV
{

    /**
     * BV Class Constructor
     *
     * The constructor takes in all the arguments via a single array.
     *
     * @access public
     * @param array
     * @return object
     */
    public function __construct($params = array())
    {
        // check to make sure we have the required parameters
        if (empty($params) OR !$params['deployment_zone_id'] OR !$params['product_id']) {
            throw new Exception('BV Class missing required parameters.
             BV expects an array with the following indexes: deployment_zone_id (string) and product_id
             (string). ');
        }

        // config array, defaults are defined here
        $this->config = array(
            'staging' => FALSE,
            'subject_type' => 'product',
            //get the current page url passed in as a "parameter"
            'current_page_url' => isset($params['current_page_url']) ? $params['current_page_url'] : "",
            'base_page_url' => $this->_getCurrentUrl(),
            'include_display_integration_code' => FALSE,
            'client_name' => $params['deployment_zone_id'],
            'internal_file_path' => FALSE,
            // used in regex to determine if request is a bot or not
            'bot_list' => 'msnbot|google|teoma|bingbot|yandexbot|yahoo',
            'ssl_enabled' => FALSE,
            'proxy_host' => '',
            'proxy_port' => '',
            'charset' => 'UTF-8',
            'seo_sdk_enabled' => TRUE,
            'execution_timeout' => 500,
            'execution_timeout_bot' => 2000,
        );

        // merge passed in params with defaults for config.
        $this->config = array_merge($this->config, $params);

        // setup the reviews object
        $this->reviews = new Reviews($this->config);

        // setup the questions object
        $this->questions = new Questions($this->config);

        // setup the stories object
        $this->stories = new Stories($this->config);

        // setup the timer object
    }

    // since this is used to set the default for an optional config option it is
    // included in the BV class.
    public function _getCurrentUrl()
    {
        // depending on protocol set the
        // beginning of url and default port
        if (isset($_SERVER["HTTPS"])) {
            $url = 'https://';
            $defaultPort = '443';
        } else {
            $url = 'http://';
            $defaultPort = '80';
        }

        $url .= $_SERVER["SERVER_NAME"];

        // if there is a port other than the defaultPort
        // being used it needs to be included
        if ($_SERVER["SERVER_PORT"] != $defaultPort) {
            $url .= ":" . $_SERVER["SERVER_PORT"];
        }

        $url .= $_SERVER["REQUEST_URI"];

        return $url;
    }

}
// end of BV class

// Most shared functionality is here so when we add support for questions
// and answers it should be minimal changes. Just need to create an answers
// class which inherits from Base.
class Base
{
    private $msg = '';

    public function __construct($params = array())
    {
        if (!$params) {
            throw new Exception('BV Base Class missing config array.');
        }

        $this->config = $params;

        // setup bv (internal) defaults
        $this->bv_config['seo-domain']['staging'] = 'seo-stg.bazaarvoice.com';
        $this->bv_config['seo-domain']['production'] = 'seo.bazaarvoice.com';

        $this->config['latency_timeout'] = $this->_isBot()
                ? $this->config['execution_timeout_bot']
                : $this->config['execution_timeout'];
    }

    /**
     * Function for collecting messages
     */
    protected function _setBuildMessage($msg)
    {
        $this->msg .= $msg . '; ';
    }

    /**
     * Return true if either seo_sdk_enabled or bvreveal flags are set, and false otherwise
     */
    private function _isSdkEnabled()
    {
        if ($this->config['seo_sdk_enabled']) {
            return true;
        } else if (isset($_GET['bvreveal']) && $_GET['bvreveal'] == 'debug') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if charset is correct, if not set to default
     */
    private function _checkCharset($seo_content)
    {
        if (isset($this->config['charset'])) {
            $supportedCharsets = mb_list_encodings();
            if (!in_array($this->config['charset'], $supportedCharsets)) {
                $this->config['charset'] = DEFAULT_CHARSET;
                $this->_setBuildMessage("Charset is not configured properly. "
                        . "BV-SEO-SDK will load default charset and continue.");
            }
        } else {
            $this->config['charset'] = DEFAULT_CHARSET;
        }
    }

    /**
     * Return encoded content with set charset
     */
    private function _charsetEncode($seo_content)
    {
        if (isset($this->config['charset'])) {
            $enc = mb_detect_encoding($seo_content);
            $seo_content = mb_convert_encoding($seo_content, $this->config['charset'], $enc);
        }

        return $seo_content;
    }

    private function _getFullSeoContents($access_method)
    {
        $seo_content = '';

        // get the page number of SEO content to load
        $page_number = $this->_getPageNumber();

        // build the URL to access the SEO content for
        // this product / page combination
        $this->seo_url = $this->_buildSeoUrl($page_number);

        // make call to get SEO payload from cloud unless seo_sdk_enabled is false
        // make call if bvreveal param in query string is set to 'debug'
        if ($this->_isSdkEnabled()) {
            $seo_content = $this->_fetchSeoContent($this->seo_url);

            $this->_checkCharset($seo_content);
            $seo_content = $this->_charsetEncode($seo_content);

            // replace tokens for pagination URLs with page_url
            $seo_content = $this->_replaceTokens($seo_content);
        }
        // show footer even if seo_sdk_enabled flag is false
        else {
            $this->_setBuildMessage('SEO SDK is disabled. '
                    . 'Enable by setting seo.sdk.enabled to true.');
        }

        $pay_load = $seo_content;

        return $pay_load;
    }

    private function _replaceSection($str, $search_str_begin, $search_str_end)
    {
        $result = $str;
        $start_index = mb_strrpos($str, $search_str_begin);

        if ($start_index !== false) {
            $end_index = mb_strrpos($str, $search_str_end);

            if ($end_index !== false) {
                $end_index += mb_strlen($search_str_end);
                $str_begin = mb_substr($str, 0, $start_index);
                $str_end = mb_substr($str, $end_index);

                $result = $str_begin . $str_end;
            }
        }

        return $result;
    }

    protected function _renderAggregateRating()
    {
        $pay_load = $this->_renderSEO('getAggregateRating');

        if ($this->_isBot()) {
            // remove reviews section from full_contents
            $pay_load = $this->_replaceSection($pay_load, '<!--begin-reviews-->', '<!--end-reviews-->');

            // remove pagination section from full contents
            $pay_load = $this->_replaceSection($pay_load, '<!--begin-pagination-->', '<!--end-pagination-->');
        }

        return $pay_load;
    }

    protected function _renderReviews()
    {
        $pay_load = $this->_renderSEO('getReviews');

        if ($this->_isBot()) {
            // remove aggregate rating section from full_contents
            $pay_load = $this->_replaceSection($pay_load, '<!--begin-aggregate-rating-->', '<!--end-aggregate-rating-->');

            // Remove schema.org product text from reviews if it exists
            $schema_org_text = "itemscope itemtype=\"http://schema.org/Product\"";
            $pay_load = mb_ereg_replace($schema_org_text, '', $pay_load);
        }

        return $pay_load;
    }

    /**
     * Render SEO
     *
     * Method used to do all the work to fetch, parse, and then return
     * the SEO payload. This is set as protected so classes inheriting
     * from the base class can invoke it or replace it if needed.
     *
     * @access protected
     * @return string
     */
    protected function _renderSEO($access_method)
    {
        $pay_load = '';

        $isBot = $this->_isBot();

        if (!$isBot && $this->config['latency_timeout'] == 0) {
            $this->_setBuildMessage("EXECUTION_TIMEOUT is set to 0 ms; JavaScript-only Display.");
        } else {

            if ($isBot && $this->config['latency_timeout'] < 100) {
                $this->config['latency_timeout'] = 100;
                $this->_setBuildMessage("EXECUTION_TIMEOUT_BOT is less than the minimum value allowed. Minimum value of 100ms used.");
            }

            try {
                BVUtility::execTimer($this->config['latency_timeout'], $isBot);
                $pay_load = $this->_getFullSeoContents($access_method);
            } catch (Exception $e) {
                $this->_setBuildMessage($e->getMessage());
            }
            BVUtility::stopTimer();
        }

        $pay_load .= $this->_buildComment($access_method);
        return $pay_load;
    }

    // --------------------------------------------------------------------
    /*  Private methods. Internal workings of SDK.                       */
    //--------------------------------------------------------------------

    /**
     * isBot
     *
     * Helper method to determine if current request is a bot or not. Will
     * use the configured regex string which can be overridden with params.
     *
     * @access private
     * @return bool
     */
    private function _isBot()
    {
        if (isset($_GET['bvreveal'])) {
            return TRUE;
        }

        // search the user agent string for an indication if this is a search bot or not
        return mb_eregi('(' . $this->config['bot_list'] . ')', $_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * getPageNumber
     *
     * Helper method to pull from the URL the page of SEO we need to view.
     *
     * @access private
     * @return int
     */
    private function _getPageNumber()
    {
        // default to page 1 if a page is not specified in the URL
        $page_number = 1;

        if (!empty($this->config['current_page_url'])) {
            //parse the current url that's passed in via the parameters
            $currentUrlArray = parse_url($this->config['current_page_url']);

            $query = $currentUrlArray['path']; //get the path out of the parsed url array

            mb_parse_str($query, $bvcurrentpagedata);  //parse the sub url such that you get the important part...page number
        }

        // bvpage is not currently implemented
        if (isset($_GET['bvpage'])) {
            $page_number = (int) $_GET['bvpage'];

            // remove the bvpage parameter from the base URL so we don't keep appending it
            $seo_param = mb_ereg_replace('/', '\/', $_GET['bvrrp']); // need to escape slashes for regex
            $this->config['base_page_url'] = mb_ereg_replace('[?&]bvrrp=' . $seo_param, '', $this->config['base_page_url']);
        }
        // other implementations use the bvrrp, bvqap, or bvsyp parameter ?bvrrp=1234-en_us/reviews/product/2/ASF234.htm
        else if (isset($_GET['bvrrp']) OR isset($_GET['bvqap']) OR isset($_GET['bvsyp'])) {
            if (isset($_GET['bvrrp'])) {
                $bvparam = $_GET['bvrrp'];
            } else if (isset($_GET['bvqap'])) {
                $bvparam = $_GET['bvqap'];
            } else {
                $bvparam = $_GET['bvsyp'];
            }
        } else if (isset($bvcurrentpagedata)) {  //if the base url doesn't include the page number information and the current url
            //is defined then use the data from the current URL.
            if (isset($bvcurrentpagedata['bvpage'])) {
                $page_number = (int) $bvcurrentpagedata['bvpage'];
                $bvparam = $bvcurrentpagedata['bvpage'];
                // remove the bvpage parameter from the base URL so we don't keep appending it
                $seo_param = mb_ereg_replace('/', '\/', $_GET['bvrrp']); // need to escape slashses for regex
                $this->config['base_page_url'] = mb_ereg_replace('[?&]bvrrp=' . $seo_param, '', $this->config['base_page_url']);
            }
            // other implementations use the bvrrp, bvqap, or bvsyp parameter ?bvrrp=1234-en_us/reviews/product/2/ASF234.htm
            else if (isset($bvcurrentpagedata['bvrrp']) || isset($bvcurrentpagedata['bvqap']) || isset($bvcurrentpagedata['bvsyp'])) {
                if (isset($bvcurrentpagedata['bvrrp'])) {
                    $bvparam = $bvcurrentpagedata['bvrrp'];
                } else if (isset($bvcurrentpagedata['bvqap'])) {
                    $bvparam = $bvcurrentpagedata['bvqap'];
                } else {
                    $bvparam = $bvcurrentpagedata['bvsyp'];
                }
            }
        }

        if (!empty($bvparam)) {
            mb_ereg('\/(\d+?)\/[^\/]+$', $bvparam, $page_number);
            $page_number = max(1, (int) $page_number[1]);

            // remove the bvrrp parameter from the base URL so we don't keep appending it
            $seo_param = mb_ereg_replace('/', '\/', $bvparam); // need to escape slashes for regex
            $this->config['base_page_url'] = mb_ereg_replace('[?&]bvrrp=' . $seo_param, '', $this->config['base_page_url']);
        }

        return $page_number;
    }

// end of _getPageNumber()

    /**
     * buildSeoUrl
     *
     * Helper method to that builds the URL to the SEO payload
     *
     * @access private
     * @param int (page number)
     * @return string
     */
    private function _buildSeoUrl($page_number)
    {
        // are we pointing at staging or production?
        if ($this->config['staging']) {
            $hostname = $this->bv_config['seo-domain']['staging'];
        } else {
            $hostname = $this->bv_config['seo-domain']['production'];
        }

        $url_scheme = $this->config['ssl_enabled'] ? 'https://' : 'http://';

        // dictates order of URL
        $url_parts = array(
            $url_scheme . $hostname,
            $this->config['cloud_key'],
            $this->config['deployment_zone_id'],
            $this->config['bv_product'],
            $this->config['subject_type'],
            $page_number
        );

        if (isset($this->config['content_sub_type']) && !empty($this->config['content_sub_type'])) {
            $url_parts[] = $this->config['content_sub_type'];
        }

        $url_parts[] = urlencode($this->config['product_id']) . '.htm';

        // if our SEO content source is a file path
        // we need to remove the first two sections
        // and prepend the passed in file path
        if ($this->config['internal_file_path']) {
            unset($url_parts[0]);
            unset($url_parts[1]);

            return $this->config['internal_file_path'] . implode("/", $url_parts);
        }

        // implode will convert array to a string with / in between each value in array
        return implode("/", $url_parts);
    }

    private function _fetchSeoContent($resource)
    {
        if (!empty($this->config['internal_file_path'])) {
            return $this->_fetchFileContent($resource);
        } else {
            return $this->_fetchCloudContent($resource);
        }
    }

    /**
     * fetchFileContent
     *
     * Helper method that will take in a file path and return it's payload while
     * handling the possible errors or exceptions that can happen.
     *
     * @access private
     * @param string (valid file path)
     * @return string (contents of file)
     */
    private function _fetchFileContent($path)
    {
        $this->_setBuildMessage('Local file content was uploaded');
        return file_get_contents($path);
    }

    /**
     * fetchCloudContent
     *
     * Helper method that will take in a URL and return it's payload while
     * handling the possible errors or exceptions that can happen.
     *
     * @access private
     * @param string (valid url)
     * @return string
     */
    private function _fetchCloudContent($url)
    {

        // is cURL installed yet?
        // if ( ! function_exists('curl_init')){
        //    return '<!-- curl library is not installed -->';
        // }
        // create a new cURL resource handle
        $ch = curl_init();

        // Set URL to download
        curl_setopt($ch, CURLOPT_URL, $url);
        // Set a referer as coming from the current page url
        curl_setopt($ch, CURLOPT_REFERER, $this->config['current_page_url']);
        // Include header in result? (0 = yes, 1 = no)
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // Should cURL return or print out the data? (true = return, false = print)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, ($this->config['latency_timeout'] / 1000));

        if ($this->config['proxy_host'] != '') {
            curl_setopt($ch, CURLOPT_PROXY, $this->config['proxy_host']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->config['proxy_port']);
        }

        // make the request to the given URL and then store the response,
        // request info, and error number
        // so we can use them later
        $request = array(
            'response' => curl_exec($ch),
            'info' => curl_getinfo($ch),
            'error_number' => curl_errno($ch),
            'error_message' => curl_error($ch)
        );

        // Close the cURL resource, and free system resources
        curl_close($ch);

        // see if we got any errors with the connection
        if ($request['error_number'] != 0) {
            $this->_setBuildMessage('Error - ' . $request['error_message']);
        }

        // see if we got a status code of something other than 200
        if ($request['info']['http_code'] != 200) {
            $this->_setBuildMessage('HTTP status code of '
                    . $request['info']['http_code'] . ' was returned');
            return '';
        }

        // if we are here we got a response so let's return it
        $this->response_time = round($request['info']['total_time'] * 1000);
        return $request['response'];
    }

    /**
     * replaceTokens
     *
     * After we have an SEO payload we need to replace the {INSERT_PAGE_URI}
     * tokens with the current page url so pagination works.
     *
     * @access private
     * @param string (valid url)
     * @return string
     */
    private function _replaceTokens($content)
    {
        // determine if query string exists in current page url
        if (parse_url($this->config['base_page_url'], PHP_URL_QUERY) != '') {
            // append an ampersand, because the URL already has a ? mark
            $page_url_query_prefix = '&';
        } else {
            // append a question mark, since this URL currently has no query
            $page_url_query_prefix = '?';
        }

        $content = mb_ereg_replace('{INSERT_PAGE_URI}', $this->config['base_page_url'] . $page_url_query_prefix, $content);

        return $content;
    }

    private function _buildComment($access_method)
    {
        $footer = '<ul id="BVSEOSDK" style="display:none;">';
        $footer .= "\n" . '	<li id="vn">bvseo-1.0.1.8-beta</li>';
        $footer .= "\n" . '	<li id="sl">bvseo-p</li>';
        if (!empty($this->config['internal_file_path'])) {
            $footer .= "\n" . '	<li id="mt">bvseo-FILE</li>';
        } else {
            $footer .= "\n" . '	<li id="mt">bvseo-CLOUD</li>';
        }
        if (isset($this->response_time)) {
            $footer .= "\n" . '	<li id="et">bvseo-' . $this->response_time . 'ms</li>';
        }
        $footer .= "\n" . '	<li id="ct">bvseo-' . mb_strtoupper($this->config['bv_product']) . '</li>';
        $footer .= "\n" . '	<li id="st">bvseo-' . mb_strtoupper($this->config['subject_type']) . '</li>';
        $footer .= "\n" . "	<li id='am'>bvseo-$access_method</li>";
        if (mb_strlen($this->msg) > 0) {
            $footer .= "\n" . '	<li id="ms">bvseo-msg: ' . $this->msg . '</li>';
        }
        $footer .= "\n" . '</ul>';

        //when in debug mode, also display the following information
        if (isset($_GET['bvreveal'])) {
            if ($_GET['bvreveal'] == 'debug') {
                $footer .= "\n" . '<ul id="BVSEOSDK_DEBUG" style="display:none;">';
                $footer .= "\n" . '   <li id="cloudKey">' . $this->config['cloud_key'] . '</li>';
                $footer .= "\n" . '   <li id="bv.root.folder">' . $this->config['deployment_zone_id'] . '</li>';
                $footer .= "\n" . '   <li id="stagingS3Hostname">' . $this->bv_config['seo-domain']['staging'] . '</li>';
                $footer .= "\n" . '   <li id="productionS3Hostname">' . $this->bv_config['seo-domain']['production'] . '</li>';
                $staging = ($this->config['staging']) ? 'TRUE' : 'FALSE';
                $footer .= "\n" . '   <li id="staging">' . $staging . '</li>';
                $footer .= "\n" . '   <li id="seo.sdk.execution.timeout">' . $this->config['latency_timeout'] . '</li>';
                $bot_detection = ($this->config['bot_detection']) ? 'TRUE' : 'FALSE';
                $footer .= "\n" . '   <li id="botDetection">' . $bot_detection . '</li>';
                $footer .= "\n" . '   <li id="crawlerAgentPattern">' . $this->config['bot_list'] . '</li>';
                $footer .= "\n" . '   <li id="userAgent">' . $_SERVER['HTTP_USER_AGENT'] . '</li>';
                $footer .= "\n" . '   <li id="pageURI">' . $this->config['current_page_url'] . '</li>';
                $footer .= "\n" . '   <li id="baseURI">' . $this->config['base_page_url'] . '</li>';
                $footer .= "\n" . '   <li id="subjectID">' . urlencode($this->config['product_id']) . '</li>';
                $footer .= "\n" . '   <li id="contentType">' . mb_strtoupper($this->config['bv_product']) . '</li>';
                $footer .= "\n" . '   <li id="subjectType">' . mb_strtoupper($this->config['subject_type']) . '</li>';
                $footer .= "\n" . '   <li id="seo.sdk.charset">' . $this->config['charset'] . '</li>';
                $footer .= "\n" . '   <li id="contentURL">' . $this->seo_url . '</li>';
                $footer .= "\n" . '</ul>';
            }
        }

        return $footer;
        // return "\n".'<!--BVSEO|dp: '.$this->config['deployment_zone_id'].'|sdk: v1.0-p|msg: '.$msg.' -->';
    }

    private function _booleanToString($boolean)
    {
        if ($boolean) {
            return 'TRUE';
        } else {
            return 'FALSE';
        }
    }

}
// end of Base class

class Reviews extends Base
{

    function __construct($params = array())
    {
        // call Base Class constructor
        parent::__construct($params);

        // since we are in the reviews class
        // we need to set the bv_product config
        // to reviews so we get reviews in our
        // SEO request
        $this->config['bv_product'] = 'reviews';

        // for reviews subject type will always
        // need to be product
        $this->config['subject_type'] = 'product';
    }

    public function getAggregateRating()
    {
        return $this->_renderAggregateRating();
    }

    public function getReviews()
    {
        return $this->_renderReviews();
    }

    public function getContent()
    {
        $pay_load = $this->_renderSEO('getContent');

        // if they want to power display integration as well
        // then we need to include the JS integration code
        if ($this->config['include_display_integration_code']) {
            $pay_load .= '
               <script>
                   $BV.ui("rr", "show_reviews", {
                       productId: "' . $this->config['product_id'] . '"
                   });
               </script>
           ';
        }

        return $pay_load;
    }

}
// end of Reviews class

class Questions extends Base
{

    function __construct($params = array())
    {
        // call Base Class constructor
        parent::__construct($params);

        // since we are in the questions class
        // we need to set the bv_product config
        // to questions so we get questions in our
        // SEO request
        $this->config['bv_product'] = 'questions';
    }

    public function getContent()
    {
        $pay_load = $this->_renderSEO('getContent');

        // if they want to power display integration as well
        // then we need to include the JS integration code
        if ($this->config['include_display_integration_code']) {

            $pay_load .= '
               <script>
                   $BV.ui("qa", "show_questions", {
                       productId: "' . $this->config['product_id'] . '"
                   });
               </script>
           ';
        }

        return $pay_load;
    }

}
// end of Questions class

class Stories extends Base
{

    function __construct($params = array())
    {
        // call Base Class constructor
        parent::__construct($params);

        // since we are in the stories class
        // we need to set the bv_product config
        // to stories so we get stories in our
        // SEO request
        $this->config['bv_product'] = 'stories';

        // for stories subject type will always
        // need to be product
        $this->config['subject_type'] = 'product';

        // for stories we have to set content sub type
        // the sub type is configured as either STORIES_LIST or STORIES_GRID
        // the folder names are "stories" and "storiesgrid" respectively.
        if (isset($this->config['content_sub_type']) && $this->config['content_sub_type'] == "stories_grid") {
            $this->config['content_sub_type'] = "storiesgrid";
        } else {
            $this->config['content_sub_type'] = "stories";
        }
    }

    public function getContent()
    {
        $pay_load = $this->_renderSeo('getContent');

        // if they want to power display integration as well
        // then we need to include the JS integration code
        if ($this->config['include_display_integration_code']) {
            $pay_load .= '
               <script>
                   $BV.ui("su", "show_stories", {
                       productId: "' . $this->config['product_id'] . '"
                   });
               </script>
           ';
        }

        return $pay_load;
    }

}
// end of Stories class

// end of bvsdk.php
