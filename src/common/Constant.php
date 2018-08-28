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

    /**
     * 关系类型
     */
    const RELATION_FRIENDS = 1;
    const RELATION_LOVERS  = 2;
    const RELATION_FAMILY  = 3;
    const RELATION_OTHER   = 4;

    /**
     * 人数限制
     */
    const MIN_PEOPLE_NUM = 1;
    const MAX_PEOPLE_NUM = 15;

    /**
     * 意向目的地
     */
    const INTENTION_TYPE_ANY         = 0;          // 哪都行
    const INTENTION_TYPE_CHINA       = 1;          // 国内
    const INTENTION_TYPE_INTERNATION = 2;          // 国外

    /**
     * 时间上的限制
     */
    const INTERVAL_TIME_DAY  = 3600 * 24;
    const INTERVAL_WAIT_JOIN = 14 * self::INTERVAL_TIME_DAY;
}