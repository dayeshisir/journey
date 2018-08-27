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
}