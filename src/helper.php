<?php namespace Tx;
/**
 * @file helper.php
 * @brief
 * @author cloud@txthinking.com
 * @version 0.0.1
 * @date 2015-03-30
 */

function i($v){ return intval(filter_var($v, FILTER_VALIDATE_INT)); }
function f($v){ return floatval(filter_var($v, FILTER_VALIDATE_FLOAT)); }
function b($v){ return (bool)($v); } // boolval >= 5.5
function s($v){ return strval($v); }
function v(){
    echo "<pre>\n";
    call_user_func_array('var_dump', func_get_args());
    exit;
}
function r($result, $error=null){
    return array(
        'error'=>$error,
        'result'=>$result,
    );
}
