<?php

/**
 * BV PHP SEO SDK Utilites
 */
class BVUtility
{

    /**
     * exec Timer
     *
     * Method used to limit execution time of the script.
     *
     * @access protected
     * @param int ($exec_time_ms) - execution time in ms
     * @param bool ($is_bot) - shows the mode in which script was run
     * @return bool
     */
    public function execTimer($exec_time_ms, $is_bot = false)
    {
        $exec_time = $exec_time_ms / 1000;
        declare(ticks = 1); // or more if 1 takes too much time
        $start = microtime(1);
        register_tick_function(function () use ($start, $exec_time, $is_bot) {
            static $once = true;
            if ((microtime(1) - $start) > $exec_time) {
                if ($once) {
                    $once = false;
                    throw new Exception('Execution timed out' . ($is_bot ? ' for search bot' : '') . ', exceeded {' . $exec_time * 1000 . '}ms;');
                }
            }
        });
    }

}