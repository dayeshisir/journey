<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午4:23
 */

namespace apps\common;


class Constant
{
    /**
     * 日志输出位置
     */
    const FILE_LOG_DIR = '../logs/';

    /**
     * 队员的身份，发起者则为队长，后续加入的则为队员
     */
    const MEMBER_TYPE_LEADER = 1;
    const MEMBER_TYPE_MEMBER = 2;
}