<?php
use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/11
 * Time: 上午12:46
 */

require "../vendor/autoload.php";

\apps\libs\Log::init();
\apps\libs\Request::init();

//Predis\Autoloader::register();
//
//$client = new Predis\Client();
//$client->incr('num');
//$iNum = $client->get('num');
//
//echo "访问人数：$iNum\n";

//$sLogFile = dirname(__FILE__) . "/../logs/ct.log";
//
//$oLog = new Logger("log");
//
//$oLog->pushHandler(new StreamHandler($sLogFile, Logger::WARNING));
//
//$oLog->warning("start");

$oCapsule = new Capsule;

$oCapsule->addConnection(require '../config/database.php');

$oCapsule->setAsGlobal();

$oCapsule->bootEloquent();

require "../config/routes.php";


