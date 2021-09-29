<?php
namespace MarckDevs\AmzShell;

use MarckDevs\SimpleLogger\SimpleLogger;

class Config{
    private static $wrapper;
    public static $_instance;
    private static $log;

    public static function instance(){
        if(!isset(self::$_instance))
            self::$_instance = new Config();
        return self::$_instance;
    }

    private function __construct(){
        self::$log = new SimpleLogger(get_class($this));
        self::$wrapper = array();
    }
    
    public function __set($name,$value){
        self::$wrapper[$name] = $value;
        self::$log->debug("SET $name = $value");
    }

    public function __get($name){
        self::$log->debug("GET $name");
        return self::$wrapper[$name];
    }
}
