<?php
namespace MarckDevs\AmzShell;

use MarckDevs\SimpleLogger\CommonsUtils;
use MarckDevs\SimpleLogger\LogLevels;
use MarckDevs\SimpleLogger\SimpleLogger;

class Console{
    
    const SHORT_ARGUMENTS = "ht:v";
    const LONG_ARGUMENTS= ['config:', 'xml:', 'cred:', "content_type:", "marketplaces:", "report:"];
    const HELP = <<<HELP
      ---| Amazon SP simple shell |---
    Usage: php amz-shell.php [OPTIONS]

        --xml XML_FILE      set the xml file to upload
        --cred CREDENTIALS  set the file with credentials
        -m "ID1 ID2"        set marketplace in quotes and space between: -m "AV2FSG2 AGHWE3R5"
        -t TYPE             set the feed type
        -g FEED_ID          get feed data
        --report DOC_ID     get xml report
        -v                  set verbose\n
    HELP;

    private static $wrapper;
    private static $instance;
    private static $log;

    private function __construct()
    {
        self::$log = new SimpleLogger(get_class($this));
        self::$wrapper = array();
    }

    public function __get($name){
        self::$log->debug("GET $name");
        return self::$wrapper[$name];
    }
    
    public function __set($name, $value){
        if(gettype($value) != 'object')
            self::$log->debug("SET $name = $value");
        self::$wrapper[$name] = $value;
    }

    public static function instance(){
        if (!isset(self::$instance)){
            self::$instance = new Console();
        }
        return self::$instance;
    }

    public function run($arg){
        $this->load($arg, $this);
        if(isset(self::$wrapper['v'])){
            SimpleLogger::set_log_level(LogLevels::INFO);
        }
        $this->checkHelp();
        if(isset(self::$wrapper["xml"])){
            if(!isset(self::$wrapper["cred"])){
                echo $this->error("\nCredentials needed and not found.\n\n");
                $this->printHelp();
            } elseif (!isset(self::$wrapper["t"])){
                echo $this->error("\nFeed type needed and not found.\n\n");
                $this->printHelp();
            }elseif(!isset(self::$wrapper["marketplaces"])){
                echo $this->error("\nMarketplaces IDs are needed and not found.\n\n");
                $this->printHelp();
            }            
            else{
                $this->uploadFeed();
            }
        }
        if(isset(self::$wrapper["g"])){
            if(!isset(self::$wrapper["cred"])){
                echo $this->error("\nCredentials needed and not found.\n\n");
                $this->printHelp();
            } elseif (!isset(self::$wrapper["t"])){
                echo $this->error("\nFeed type needed and not found.\n\n");
                $this->printHelp();
            }else{
                $this->getFeed();
            }
        }
        if(isset(self::$wrapper["report"])){
            if(!isset(self::$wrapper["cred"])){
                echo $this->error("\nCredentials needed and not found.\n\n");
                $this->printHelp();
            } elseif (!isset(self::$wrapper["t"])){
                echo $this->error("\nFeed type needed and not found.\n\n");
                $this->printHelp();
            }else{
                $this->getDoc();
            }
        }
    }

    private function load($arr, $obj){
        foreach ($arr as $key => $value) {
            $obj->{$key} = $value;
        }
    }

    private function uploadFeed(){
        $this->loadCred();
        $controller = new Controller($this->config);
        $this->config->file = $this->xml;
        $this->config->marketplaces_ids = explode(' ', $this->marketplaces);
        self::$log->info("Initialize controller");
        $controller->uploadXML();
    }

    private function getFeed(){
        $this->loadCred();
        $controller = new Controller($this->config);
        self::$log->info("Start conection to amazon");
        $controller->getFeed($this->g);
    }

    private function getDoc(){
        $this->loadCred();
        $controller = new Controller($this->config);
        self::$log->info("Start conection to amazon");
        $controller->getReport($this->report);
    }

    private function loadCred(){
        if(CommonsUtils::ends_with($this->cred,".env")){
            $data = file_get_contents($this->cred);
            self::$log->info("Load env file for credentials");
            $lines = explode("\n", $data);
            $this->config = Config::instance();
            foreach ($lines as $line) {
                $tuple = explode('=', $line);
                if(count($tuple) == 2){
                    $this->config->{$tuple[0]} = $tuple[1];
                }
            }
        }elseif ( CommonsUtils::ends_with($this->cred, ".json")){
            $data = file_get_contents($this->cred);
            self::$log->info("Load json file for credentials");
            $data_arr = json_decode($data, true);
            $this->load($data_arr, $this->config);
        }

        if(!isset($this->config->content_type)){
            $this->config->content_type = "text/xml; charset=utf-8";
        }
    }

    private function error($message){
        return "\e[0;31m$message\e[0m";
    }

    private function checkHelp()
    {
        if(array_key_exists("h", self::$wrapper)){
            $this->printHelp();
        }
    }

    private function printHelp()
    {
        echo "\e[0m";
        echo self::HELP."\n";
        exit(0);
    }
}