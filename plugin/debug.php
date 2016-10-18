<?php

/**
 * Dump a variable to the error log.
 *
 * You can pass as many variables as you want!
 */
function var_log() {
    ob_start();
    call_user_func_array( 'var_dump', func_get_args() );
    error_log( ob_get_clean() );
}