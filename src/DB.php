<?php namespace Tx;
//
// * select a db randomly
// * high available
// * if slace db all have down then read from master
// * seperate read and write
//
// Example:
// DB::getAll('select * from bd_baodian');
// DB::getRow('select * from bd_baodian limit 1');
// DB::getCol('select name from bd_baodian');
// DB::getCell('select name from bd_baodian limit 1');
// DB::exec('UPDATE page SET title="test" WHERE id=1');
//
// Author: cloud@txthinking.com
//

use \RedBeanPHP\R;

// C return config like this:
/*
array (
    'write' => array (
        array (
            'host' => '9.9.9.9',
            'port' => '3306',
            'username' => 'root',
            'password' => '111111',
            'dbname' => 'test',
        ),
    ),
    'read' => array (
        array (
            'host' => '9.9.9.9',
            'port' => '3306',
            'username' => 'root',
            'password' => '111111',
            'dbname' => 'test',
        ),
    ),
)
//*/
final class DB{
    private static $_mcs;
    private static $_scs;
    private static $_i = false;

    private function __construct(){}

    private function config(){
        return array (
            'write' => array (
                array (
                    'host' => $_ENV['MYSQL_HOST'],
                    'port' => $_ENV['MYSQL_PORT'],
                    'username' => $_ENV['MYSQL_USER'],
                    'password' => $_ENV['MYSQL_PASSWORD'],
                    'dbname' => $_ENV['MYSQL_DATABASE'],
                ),
            ),
            'read' => array (
                array (
                    'host' => $_ENV['MYSQL_HOST'],
                    'port' => $_ENV['MYSQL_PORT'],
                    'username' => $_ENV['MYSQL_USER'],
                    'password' => $_ENV['MYSQL_PASSWORD'],
                    'dbname' => $_ENV['MYSQL_DATABASE'],
                ),
            ),
        );
    }

    private static function i() {
        $c = self::config();
        shuffle($c['write']);
        shuffle($c['read']);
        self::$_mcs = $c['write'];
        self::$_scs = array_merge($c['read'], $c['write']);

        R::setup();
        R::freeze(true);
        foreach(self::$_mcs as $i=>$c){
            R::addDatabase("write:$i", sprintf('mysql:host=%s;port=%d;dbname=%s', $c['host'], $c['port'], $c['dbname']), $c['username'], $c['password']);
        }
        foreach(self::$_scs as $i=>$c){
            R::addDatabase("read:$i", sprintf('mysql:host=%s;port=%d;dbname=%s', $c['host'], $c['port'], $c['dbname']), $c['username'], $c['password']);
        }
        self::$_i = true;
    }

    // $a=read/write
    public static function c($a){
        if(!self::$_i){
            self::i();
        }
        if($a === 'write'){
            foreach(self::$_mcs as $i=>$c){
                R::selectDatabase("write:$i");
                if(R::testConnection()){
                    return;
                }
            }
            throw new \Exception('All master DB have down');
        }
        if($a === 'read'){
            foreach(self::$_scs as $i=>$c){
                R::selectDatabase("read:$i");
                if(R::testConnection()){
                    return;
                }
            }
            throw new \Exception('All slave and master DB have down');
        }
    }

    public static function __callStatic($name, array $args){
        if(in_array($name, array(
            'exec',
            ))){
            self::c('write');
            return call_user_func_array("\\RedBeanPHP\\R::$name", $args);
        }
        self::c('read');
        return call_user_func_array("\\RedBeanPHP\\R::$name", $args);
    }
}
