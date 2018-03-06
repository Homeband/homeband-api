<?php
/**
 * Created by PhpStorm.
 * User: nicolasgerard
 * Date: 17/01/18
 * Time: 18:06
 */

class MY_Exceptions extends CI_Exceptions
{
    function show_error($heading, $message, $template = 'error_general', $status_code = 500)
    {

        die(print_r($message));
        log_message( 'debug', print_r( $message, TRUE ) );
        throw new Exception(is_array($message) ? $message[0] : $message, $status_code );
    }
}