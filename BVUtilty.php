<?php

/**
 * BV PHP SEO SDK Utilites
 */

class BVUtilty
{
   /**
     * mb_substr_replace
     *
     * Multibite substr_replace().
     * https://gist.github.com/stemar/8287074
     *
     * @access public
     * @param mixed ($string)
     * @param mixed ($replacement)
     * @param mixed ($start)
     * @param mixed ($length)
     * @return string
     */
    public static function mb_substr_replace($string, $replacement, $start, $length = NULL)
    {
        if (is_array($string)) {
            $num = count($string);
            // $replacement
            $replacement = is_array($replacement) ? array_slice($replacement, 0, $num) : array_pad(array($replacement), $num, $replacement);
            // $start
            if (is_array($start)) {
                $start = array_slice($start, 0, $num);
                foreach ($start as $key => $value)
                    $start[$key] = is_int($value) ? $value : 0;
            } else {
                $start = array_pad(array($start), $num, $start);
            }
            // $length
            if (!isset($length)) {
                $length = array_fill(0, $num, 0);
            } elseif (is_array($length)) {
                $length = array_slice($length, 0, $num);
                foreach ($length as $key => $value)
                    $length[$key] = isset($value) ? (is_int($value) ? $value : $num) : 0;
            } else {
                $length = array_pad(array($length), $num, $length);
            }
            // Recursive call
            return array_map(__FUNCTION__, $string, $replacement, $start, $length);
        }
        preg_match_all('/./us', (string) $string, $smatches);
        preg_match_all('/./us', (string) $replacement, $rmatches);
        if ($length === NULL)
            $length = mb_strlen($string);
        array_splice($smatches[0], $start, $length, $rmatches[0]);
        return join($smatches[0]);
    }
}
