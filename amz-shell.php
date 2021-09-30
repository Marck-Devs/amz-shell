<?php

use MarckDevs\AmzShell\Console;
use MarckDevs\SimpleLogger\Interfaces\LogFormatter;
use MarckDevs\SimpleLogger\LogLevels;
use MarckDevs\SimpleLogger\SimpleFormatter;
use MarckDevs\SimpleLogger\SimpleLogger;

require 'vendor/autoload.php';

$arguments = getopt(Console::SHORT_ARGUMENTS,Console::LONG_ARGUMENTS);
Console::instance()->run($arguments);
