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
 *    - you are running PHP 5 or greater
 *    - you have the curl library installed
 *    - every request has the user agent header
 *    in it (if using a CDN like Akamai additional configuration
 *    maybe required).
 *
 */
/**
 * Example usage:
 *
 * require(bvsdk.php);
 *
 * $bv = new BazaarvoiceSeo\BV(array(
 *  'bv_root_folder' => '1234-en_US',
 *  'subject_id' => 'XXYYY',
 *  'cloud_key' => 'company-cdfa682b84bef44672efed074093ccd3',
 *  'staging' => FALSE
 * ));
 *
 */

namespace BazaarvoiceSeo;

require_once 'BVUtility.php';
require_once 'BVFooter.php';
require_once 'Base.php';

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
 *    bv_root_folder (string)
 *    subject_id (string)
 *    cloud_key (string)
 *
 *   Optional fields
 *    base_url (string) (defaults to detecting the base_url automatically)
 *    page_url (string) (defaults to empty, to provide query parameters )
 *    staging (boolean) (defaults to false, need to put true for testing with staging data)
 *    testing (boolean) (defaults to false, need to put true for testing with testing data)
 *    content_type (string) (defaults to reviews, you can pass content type here if needed)
 *    subject_type (string) (defaults to product, you can pass subject type here if needed)
 *    content_sub_type (string) (defaults to stories, for stories you can pass either STORIES_LIST or STORIES_GRID content type)
 *    execution_timeout (int) (in milliseconds) (defaults to 500ms, to set period of time before the BVSEO injection times out for user agents that do not match the criteria set in CRAWLER_AGENT_PATTERN)
 *    execution_timeout_bot (int) (in milliseconds) (defaults to 2000ms, to set period of time before the BVSEO injection times out for user agents that match the criteria set in CRAWLER_AGENT_PATTERN)
 *    charset (string) (defaults to UTF-8, to set alternate character for SDK output)
 *    crawler_agent_pattern (string) (defaults to msnbot|googlebot|teoma|bingbot|yandexbot|yahoo)
 */
class BV {

  /**
   * BV Class Constructor
   *
   * The constructor takes in all the arguments via a single array.
   *
   * @access public
   * @param array
   * @return object
   */
  public function __construct($params = array()) {

    $this->validateParameters($params);

    // config array, defaults are defined here.
    $this->config = array(
      'staging' => FALSE,
      'testing' => FALSE,
      'content_type' => isset($params['content_type']) ? $params['content_type'] : 'reviews',
      'subject_type' => isset($params['subject_type']) ? $params['subject_type'] : 'product',
      'page_url' => isset($params['page_url']) ? $params['page_url'] : '',
      'base_url' => isset($params['base_url']) ? $params['base_url'] : '',
      'include_display_integration_code' => FALSE,
      'client_name' => $params['bv_root_folder'],
      'local_seo_file_root' => '',
      'load_seo_files_locally' => FALSE,
      // used in regex to determine if request is a bot or not
      'crawler_agent_pattern' => 'msnbot|google|teoma|bingbot|yandexbot|yahoo',
      'ssl_enabled' => FALSE,
      'proxy_host' => '',
      'proxy_port' => '',
      'charset' => 'UTF-8',
      'seo_sdk_enabled' => TRUE,
      'execution_timeout' => 500,
      'execution_timeout_bot' => 2000,
      'bvreveal' => isset($params['bvreveal']) ? $params['bvreveal'] : '',
      'page' => 1,
      'page_params' => array()
    );

    // Merge passed in params with defaults for config.
    $this->config = array_merge($this->config, $params);

    // Obtain all the name=value parameters from either the page URL passed in,
    // or from the actual page URL as seen by PHP. Parameter values from the
    // actual URL override those from the URL passed in, as that is usually a
    // trucated URL where present at all.
    //
    // Note that we're taking parameters from query string, fragment, or
    // _escaped_fragment_. (Though fragment is not passed to the server, so
    // we won't actually see that in practice).
    //
    // We're after bvrrp, bvqap, bvsyp, and bvstate, but sweep up everything
    // while we're here.
    if (isset($params['page_url'])) {
      $this->config['bv_page_data'] = BVUtility::parseUrlParameters($params['page_url']);
    }

    // Extract bvstate if present and parse that into a set of useful values.
    if (isset($this->config['bv_page_data']['bvstate'])) {
      $this->config['page_params'] = BVUtility::getBVStateParams($this->config['bv_page_data']['bvstate']);
    }

    // Remove any trailing URL delimeters from the base URL. E.g.:
    // http://example.com?
    // http://example.com?a=b&
    // http://example.com?a=b&_escaped_fragment_=x/y/z?r=s%26
    //
    $this->config['base_url'] = mb_ereg_replace('(&|\?|%26)$', '', $this->config['base_url']);

    // Get rid of all the other things we care about from the base URL, so that
    // we don't double up the parameters.
    $this->config['base_url'] = BVUtility::removeUrlParam($this->config['base_url'], 'bvstate');
    $this->config['base_url'] = BVUtility::removeUrlParam($this->config['base_url'], 'bvrrp');
    $this->config['base_url'] = BVUtility::removeUrlParam($this->config['base_url'], 'bvqap');
    $this->config['base_url'] = BVUtility::removeUrlParam($this->config['base_url'], 'bvsyp');

    // Create the processor objects.
    $this->reviews = new Reviews($this->config);
    $this->questions = new Questions($this->config);
    $this->stories = new Stories($this->config);
    $this->spotlights = new Spotlights($this->config);
    $this->sellerratings = new SellerRatings($this->config);

    // Assign one to $this->SEO based on the content type.
    $ct = isset($this->config['page_params']['content_type']) ? $this->config['page_params']['content_type'] : $this->config['content_type'];
    if (isset($ct)) {
      switch ($ct) {
        case 'reviews': {
          $st = isset($this->config['page_params']['subject_type']) ? $this->config['page_params']['subject_type'] : $this->config['subject_type'];
          if (isset($st) && $st == 'seller') {
            $this->SEO = $this->sellerratings;
          } else {
            $this->SEO = $this->reviews;
          }
          break;
        }
        case 'questions': $this->SEO = $this->questions;
          break;
        case 'stories': $this->SEO = $this->stories;
          break;
        case 'spotlights': $this->SEO = $this->spotlights;
          break;
        default:
          throw new \Exception('Invalid content_type value provided: ' . $this->config['content_type']);
      }
    }
  }

  protected function validateParameters($params) {
    if (!is_array($params)) {
      throw new \Exception(
        'BV class constructor argument $params must be an array.'
      );
    }

    // check to make sure we have the required parameters.
    if (empty($params['bv_root_folder'])) {
      throw new \Exception(
        'BV class constructor argument $params is missing required bv_root_folder key. An ' .
        'array containing bv_root_folder (string) is expected.'
        );
    }

    if (empty($params['subject_id'])) {
      throw new \Exception(
        'BV class constructor argument $params is missing required subject_id key. An ' .
        'array containing subject_id (string) is expected.'
      );
    }
  }
}
// end of BV class

/**
 * Reviews Class
 *
 * Base class extention for work with "reviews" content type.
 */
class Reviews extends Base {

  function __construct($params = array()) {
    // call Base Class constructor
    parent::__construct($params);

    // since we are in the reviews class
    // we need to set the content_type config
    // to reviews so we get reviews in our
    // SEO request
    $this->config['content_type'] = 'reviews';

    // for reviews subject type will always
    // need to be product
    $this->config['subject_type'] = 'product';
  }

  public function getAggregateRating() {
    return $this->_renderAggregateRating();
  }

  public function getReviews() {
    return $this->_renderReviews();
  }

  public function getContent() {
    $payload = $this->_renderSEO('getContent');

    if (!empty($this->config['page_params']['subject_id']) && $this->_checkBVStateContentType()) {
      $subject_id = $this->config['page_params']['subject_id'];
    } else {
      $subject_id = $this->config['subject_id'];
    }
    // if they want to power display integration as well
    // then we need to include the JS integration code
    if ($this->config['include_display_integration_code']) {
      $payload .= '
         <script>
           $BV.ui("rr", "show_reviews", {
             productId: "' . $subject_id . '"
           });
         </script>
       ';
    }

    return $payload;
  }

}
// end of Reviews class

/**
 * Questions Class
 *
 * Base class extention for work with "questions" content type.
 */
class Questions extends Base {

  function __construct($params = array()) {
    // call Base Class constructor
    parent::__construct($params);

    // since we are in the questions class
    // we need to set the content_type config
    // to questions so we get questions in our
    // SEO request
    $this->config['content_type'] = 'questions';
  }

  public function getContent() {
    $payload = $this->_renderSEO('getContent');
    if (!empty($this->config['page_params']['subject_id']) && $this->_checkBVStateContentType()) {
      $subject_id = $this->config['page_params']['subject_id'];
    } else {
      $subject_id = $this->config['subject_id'];
    }
    // if they want to power display integration as well
    // then we need to include the JS integration code
    if ($this->config['include_display_integration_code']) {

      $payload .= '
         <script>
           $BV.ui("qa", "show_questions", {
             productId: "' . $subject_id . '"
           });
         </script>
       ';
    }

    return $payload;
  }

}
// end of Questions class

/**
 * Stories Class
 *
 * Base class extention for work with "stories" content type.
 */
class Stories extends Base {

  function __construct($params = array()) {
    // call Base Class constructor
    parent::__construct($params);

    // since we are in the stories class
    // we need to set the content_type config
    // to stories so we get stories in our
    // SEO request
    $this->config['content_type'] = 'stories';

    // for stories subject type will always
    // need to be product
    $this->config['subject_type'] = 'product';

    // for stories we have to set content sub type
    // the sub type is configured as either STORIES_LIST or STORIES_GRID
    // the folder names are "stories" and "storiesgrid" respectively.
    if (isset($this->config['content_sub_type'])
        && $this->config['content_sub_type'] == "stories_grid") {
      $this->config['content_sub_type'] = "storiesgrid";
    } else {
      $this->config['content_sub_type'] = "stories";
    }
  }

  public function getContent() {
    $payload = $this->_renderSEO('getContent');
    if (!empty($this->config['page_params']['subject_id']) && $this->_checkBVStateContentType()) {
      $subject_id = $this->config['page_params']['subject_id'];
    } else {
      $subject_id = $this->config['subject_id'];
    }
    // if they want to power display integration as well
    // then we need to include the JS integration code
    if ($this->config['include_display_integration_code']) {
      $payload .= '
         <script>
           $BV.ui("su", "show_stories", {
             productId: "' . $subject_id . '"
           });
         </script>
       ';
    }

    return $payload;
  }

}
// end of Stories class

class Spotlights extends Base {

  function __construct($params = array()) {
    // call Base Class constructor
    parent::__construct($params);

    // since we are in the spotlights class
    // we need to set the content_type config
    // to spotlights so we get reviews in our
    // SEO request
    $this->config['content_type'] = 'spotlights';

    // for spotlights subject type will always
    // need to be category
    $this->config['subject_type'] = 'category';
  }

  public function getContent() {
    return $this->_renderSEO('getContent');
  }

}
// end of Spotlights class

class SellerRatings extends Base {

    function __construct($params = array()) {

        // call Base Class constructor
        parent::__construct($params);

        // since we are in the Seller Rating class
        // we need to set the content_type config
        // to reviews so we get reviews in our
        // SEO request
        $this->config['content_type'] = 'reviews';

        // for seller rating subject type will always
        // need to be seller
        $this->config['subject_type'] = 'seller';

    }

    public function getContent() {
        return $this->_renderSEO('getContent');
    }

}
// end of Spotlights class


// end of bvseosdk.php
