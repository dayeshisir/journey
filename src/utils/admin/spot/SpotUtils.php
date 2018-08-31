<?php
namespace apps\utils\admin;
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/23
 * Time: 下午8:17
 */

class SpotUtils
{
    protected static $_aMap = array(
        // 'name'       => '',
        'desc'     => '',
        'mdd_name'   => '',
        'w_id'       => 0,
        'min_num'    => 0,
        'max_num'    => 0,
        'time'   => '2018-01-01',
        'relation'   => 0,
        'min_budget' => 0,
        'max_budget' => 0,
        'spread'     => '',
    );

    /**
     * 获取参数
     *
     * @return array
     */
    public static function aGetParam()
    {
        $aParam   = array();
        foreach (self::$_aMap as $key => $value) {
            $aParam[$key] = \apps\libs\Request::mGetParam($key, $value);
        }

        return $aParam;
    }
}