<?php

use MarckDevs\AmzShell\Console;
use MarckDevs\SimpleLogger\Interfaces\LogFormatter;
use MarckDevs\SimpleLogger\LogLevels;
use MarckDevs\SimpleLogger\SimpleFormatter;
use MarckDevs\SimpleLogger\SimpleLogger;

require 'vendor/autoload.php';

class Format implements LogFormatter{
   public function format($string, $data = []):string {
        $format = "{date}  {lvl} - {msg}";
        return SimpleFormatter::set_data($format,
         SimpleFormatter::gen_arr($string, $data));
   }
}

SimpleLogger::set_log_format(new Format());
$arguments = getopt(Console::SHORT_ARGUMENTS,Console::LONG_ARGUMENTS);
Console::instance()->run($arguments);