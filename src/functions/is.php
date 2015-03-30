<?php namespace Tx;
/**
 * @file is.php
 * @brief
 * @author cloud@txthinking.com
 * @version 0.0.1
 * @date 2015-03-30
 */

function isBankCard($n){
    $n = strval($n);
    $sum = 0;
    for($i=1; $i<strlen($n); $i++){
        $now = intval($n[strlen($n)-1-$i]);
        if($i%2 === 0){
            $sum += $now;
            continue;
        }
        $_ = $now*2;
        if($_ >= 10){
            $_0 = strval($_);
            $_ = intval($_0[0]) + intval($_0[1]);
        }
        $sum += $_;
    }
    if(($sum + intval($n[strlen($n)-1]))%10 !== 0){
        return false;
    }
    return true;
}

function isChineseID($id){
    $id = strrev($id);
    if(strlen($id) !== 18){
        return false;
    }
    $sum = 0;
    for($i=1; $i<strlen($id); $i++){
        $w = pow(2, $i+1-1)%11;
        $sum += intval($id[$i]) * $w;
    }
    $v = (12-($sum%11))%11;
    if($v === 10){
        return strtolower(strval($id[0])) === 'x';
    }
    return intval($id[0]) === $v;
}

function isEmail($email){
   $r =  preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $email);
   return $r === 1;
}

function isChineseWords($words){
   $r =  preg_match('/[\x{4e00}-\x{9fa5}]+/u', $words);
   return $r === 1;
}

