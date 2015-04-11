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
use \Dotenv;

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
class DB{
    private static $_mcs;
    private static $_scs;
    private static $_inited = false;

    private function __construct(){}

    public static function conf(){
        Dotenv::required(array('MYSQL_HOST', 'MYSQL_PORT', 'MYSQL_USER', 'MYSQL_PASSWORD', 'MYSQL_DATABASE'));
        return array (
            'write' => array (
                array (
                    'host' => isset($_ENV['WRITE_MYSQL_HOST']) ? $_ENV['WRITE_MYSQL_HOST'] :  $_ENV['MYSQL_HOST'],
                    'port' => isset($_ENV['WRITE_MYSQL_PORT']) ? $_ENV['WRITE_MYSQL_PORT'] :  $_ENV['MYSQL_PORT'],
                    'username' => isset($_ENV['WRITE_MYSQL_USER']) ? $_ENV['WRITE_MYSQL_USER'] :  $_ENV['MYSQL_USER'],
                    'password' => isset($_ENV['WRITE_MYSQL_PASSWORD']) ? $_ENV['WRITE_MYSQL_PASSWORD'] : $_ENV['MYSQL_PASSWORD'],
                    'dbname' => isset($_ENV['WRITE_MYSQL_DATABASE']) ? $_ENV['WRITE_MYSQL_DATABASE'] : $_ENV['MYSQL_DATABASE'],
                ),
            ),
            'read' => array (
                array (
                    'host' => isset($_ENV['READ_MYSQL_HOST']) ? $_ENV['READ_MYSQL_HOST'] :  $_ENV['MYSQL_HOST'],
                    'port' => isset($_ENV['READ_MYSQL_PORT']) ? $_ENV['READ_MYSQL_PORT'] :  $_ENV['MYSQL_PORT'],
                    'username' => isset($_ENV['READ_MYSQL_USER']) ? $_ENV['READ_MYSQL_USER'] :  $_ENV['MYSQL_USER'],
                    'password' => isset($_ENV['READ_MYSQL_PASSWORD']) ? $_ENV['READ_MYSQL_PASSWORD'] : $_ENV['MYSQL_PASSWORD'],
                    'dbname' => isset($_ENV['READ_MYSQL_DATABASE']) ? $_ENV['READ_MYSQL_DATABASE'] : $_ENV['MYSQL_DATABASE'],
                ),
            ),
        );
    }

    protected static function init() {
        $c = self::conf();
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
        self::$_inited = true;
    }

    // $a=read/write
    protected static function select($a){
        if(!self::$_inited){
            self::init();
        }
        if($a === 'write'){
            foreach(self::$_mcs as $i=>$c){
                R::selectDatabase("write:$i");
                if(R::testConnection()){
                    return;
                }
            }
            throw new \Exception('Master DB have down');
        }
        if($a === 'read'){
            foreach(self::$_scs as $i=>$c){
                R::selectDatabase("read:$i");
                if(R::testConnection()){
                    return;
                }
            }
            throw new \Exception('Slave and master DB have down');
        }
    }

    public static function __callStatic($name, array $args){
        if(in_array($name, array(
            'exec',
            ))){
            self::select('write');
            return call_user_func_array("\\RedBeanPHP\\R::$name", $args);
        }
        self::select('read');
        return call_user_func_array("\\RedBeanPHP\\R::$name", $args);
    }
}
