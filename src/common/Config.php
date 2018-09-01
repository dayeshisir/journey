<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/27
 * Time: 下午10:10
 */

namespace apps\common;


class Config
{
    public static $aRedisConf = [
        'scheme' => 'tcp',
        'host'   => '127.0.01',
        'port'   => 6379,
    ];
}