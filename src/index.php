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

date_default_timezone_set("Asia/Shanghai");

\apps\libs\Log::init();
\apps\libs\Request::init();

$oCapsule = new Capsule;

$oCapsule->addConnection(require '../config/database.php');

$oCapsule->setAsGlobal();

$oCapsule->bootEloquent();

require "../config/routes.php";


