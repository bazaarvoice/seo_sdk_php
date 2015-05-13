<?php

/**
 * tick_timer
 *
 * Tick function for execTimer
 *
 * @param int ($start) - start time in ms
 * @param int ($exec_time_ms) - execution time in ms
 * @param bool ($is_bot) - shows the mode in which script was run
 */
function tick_timer($start, $exec_time, $is_bot)
{
  static $once = true;
  if ((microtime(1) - $start) > $exec_time) {
    if ($once) {
      $once = false;
      throw new \Exception('Execution timed out' . ($is_bot ? ' for search bot' : '') . ', exceeded ' . $exec_time * 1000 . 'ms');
    }
  }
}

/**
 * BV PHP SEO SDK Utilites
 */
class BVUtility
{
  public static $supportedContentTypes = array(
    'r' => 'REVIEWS',
    'q' => 'QUESTIONS',
    's' => 'STORIES',
    'u' => 'UNIVERSAL',
    'sp'=> 'SPOTLIGHTS'
  );
  private static $supportedSubjectTypes = array(
    'p' => 'PRODUCT',
    'c' => 'CATEGORY',
    'e' => 'ENTRY',
    'd' => 'DETAIL'
  );

  /**
   * execTimer
   *
   * Method used to limit execution time of the script.
   *
   * @access public
   * @param int ($exec_time_ms) - execution time in ms
   * @param bool ($is_bot) - shows the mode in which script was run
   */
  public static function execTimer($exec_time_ms, $is_bot = false, $start = 0)
  {
    $exec_time = $exec_time_ms / 1000;
    declare(ticks = 1); // or more if 1 takes too much time
    if (empty($start)) {
      $start = microtime(1);
    }
    register_tick_function('tick_timer', $start, $exec_time, $is_bot);
  }

  /**
   * stopTimer
   *
   * Method used to stop execution time checker.
   *
   * @access public
   */
  public static function stopTimer()
  {
    unregister_tick_function('tick_timer');
  }

  /**
   * filterGetInput
   *
   * Get $_GET['bvstate'] variable.
   *
   * @access public
   * @return string - value of $_GET['bvstate'] variable on success, FALSE if the filter fails.
   */
  public static function filterGetInput()
  {
    return filter_input(INPUT_GET, 'bvstate');
  }

  /**
   * getBVStateHash
   *
   * If "bvstate" parameter was set by GET the method method qets and parses it.
   *
   * @access public
   * @return array - parsed "bvstate" parameters.
   */
  public static function getBVStateHash($bvstate = '')
  {
    $bvStateHash = array();
    if (empty($bvstate)) {
      $bvstate = static::filterGetInput();
    }
    if (!empty($bvstate)) {
      $bvp = mb_split("/", $bvstate);
      foreach ($bvp as $param) {
        $key = static::mb_trim(mb_substr($param, 0, mb_strpos($param, ':')));
        $bvStateHash[$key] = static::mb_trim(mb_substr($param, mb_strpos($param, ':') + 1));
      }
    }
    return $bvStateHash;
  }

  /**
   * checkType
   *
   * Checks content type or subject type is supported.
   * If type is not supported throw exception.
   *
   * @access public
   * @param string ($type) - content type or subject type which have to be checked.
   * @param string ($typeType) - default 'ct', mark of type 'ct' - content type, 'st' - subject type
   * @return boolean True if type is correct and no exception was thrown.
   */
  public static function checkType($type, $typeType = 'ct')
  {
    if ($typeType == 'st') {
      $typeName = 'subject type';
      $typeArray = static::$supportedSubjectTypes;
    } else {
      $typeName = 'content type';
      $typeArray = static::$supportedContentTypes;
    }
    if (!array_key_exists(mb_strtolower($type), $typeArray)) {
      foreach ($typeArray as $key => $value) {
        $supportList[] = $key . '=' . $value;
      }
      throw new Exception('Obtained not supported ' . $typeName
      . '. BV Class supports following ' . $typeName . ': '
      . implode(', ', $supportList));
    }

    return true;
  }

  /**
   * getBVStateParams
   *
   * Fills in script parameters according "bvstate" parameters.
   *
   * @access public
   * @param array ($bvStateHash) - parsed array of "bvstate" parameters.
   * @return array - parameters that are ready to use in script.
   */
  public static function getBVStateParams($bvStateHash)
  {
    $params = array();
    if (!empty($bvStateHash)) {
      if (!empty($bvStateHash['id'])) {
        $params['subject_id'] = $bvStateHash['id'];
      }
      if (!empty($bvStateHash['pg'])) {
        $params['page'] = $bvStateHash['pg'];
      }
      if (!empty($bvStateHash['ct'])) {
        $cType = $bvStateHash['ct'];
        self::checkType($cType, 'ct');
        $params['content_type'] = mb_strtolower(self::$supportedContentTypes[$cType]);
      }
      if (!empty($bvStateHash['st'])) {
        $sType = $bvStateHash['st'];
        self::checkType($sType, 'st');
        $params['subject_type'] = mb_strtolower(self::$supportedSubjectTypes[$sType]);
      }
      if (!empty($bvStateHash['reveal'])) {
        $params['bvreveal'] = $bvStateHash['reveal'];
      }
    }

    return $params;
  }

   /**
   * removeUrlParam
   *
   * Remove parmeters from the URL
   *
   * @access public
   * @param string ($url) - The input URL
   * @param string ($paramName) - Paremeter name whicj should be removed.
   * @return string - updated URL
   */
  public static function removeUrlParam($url, $paramName)
  {
    $urlElements = mb_split("\?", $url);
    if (!empty($urlElements[1])) {
      mb_parse_str($urlElements[1], $queryElements);
      unset($queryElements[$paramName]);
      $newUrl = $urlElements[0];
      if (!empty($queryElements)) {
        $newUrl .= '?' . http_build_query($queryElements);
      }
    } else {
      $newUrl = $url;
    }

    return $newUrl;
  }

  /**
   * mb_trim
   *
   * Multibyte trim.
   * (http://stackoverflow.com/questions/10066647/multibyte-trim-in-php/10067670#10067670)
   *
   * @access public
   * @param sting ($str) - The string that will be trimmed.
   * @return string -  The trimmed string.
   */
  public static function mb_trim($str)
  {
    return preg_replace("/(^\s+)|(\s+$)/us", "", $str);
  }

}
