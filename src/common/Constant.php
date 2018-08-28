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
    const RELATION_FAMILY  = 4;
    const RELATION_OTHER   = 8;

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

    /**
     * 各种状态
     */
    const JOURNEY_STATUS_INIT = 0;      // 创建伊始
    const JOURNEY_STATUS_JOIN = 1;      // 组局中，等待队员加入
    const JOURNEY_STATUS_VOTE = 2;      // 队员都加入了 或者 局头 确认局成了
    const JOURNEY_STATUS_SUCC = 3;      // 成功
    const JOURNEY_STATUS_FAIL = 4;      // 失败
}