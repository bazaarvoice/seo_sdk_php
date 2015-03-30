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

}