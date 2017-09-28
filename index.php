<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(120000);
ini_set('memory_limit','2048M');

require(__DIR__."/vendor/autoload.php");
require(__DIR__."/bootstrap/db.php");


# Set Root Directory
DEFINE("root",__DIR__);

$file = __DIR__."/storage/build.pdf";
$P =  new \app\runController($file);

$out = \app\services\cacheService::getCache("out");
echo "\n\n".__LINE__."--out-->".json_encode($out,JSON_PRETTY_PRINT);