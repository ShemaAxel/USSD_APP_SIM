<?php

define('BASE_DIR', dirname(__DIR__));

if (!defined('WEB_ROOT'))
    define('WEB_ROOT', BASE_DIR . '/interfaces');

//require_once('/var/www/html/log4php/Logger.php');

if (!defined('CONFIGS_ROOT'))
    define('CONFIGS_ROOT', WEB_ROOT . '/configs');

if (!defined('UTILS_ROOT'))
    define('UTILS_ROOT', WEB_ROOT . '/utils');

if (!defined('BENCHMARK_ROOT'))
    define('BENCHMARK_ROOT', WEB_ROOT . '/benchmark');

require_once CONFIGS_ROOT . '/UssdConfigs.php';
require_once CONFIGS_ROOT . '/dbConfigs.php';

require_once UTILS_ROOT . '/DBUtils.php';
require_once UTILS_ROOT . '/CoreUtils.php';
require_once BENCHMARK_ROOT .'/BenchMark.php';
require_once 'USSDRouter.php';
require_once 'USSDActivity.php';
?>
