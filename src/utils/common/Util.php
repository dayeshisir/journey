<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/1
 * Time: 上午11:08
 */

namespace apps\utils\common;


class Util
{
    /**
     * 数组转为对象
     *
     * @param $aParam
     * @param $filed
     * @return array
     */
    public static function array2map($aParam, $filed)
    {
        $aRet = [];
        if (empty($aParam)) {
            return $aRet;
        }

        foreach ($aParam as $item) {
            if (array_key_exists($filed, $item)) {
                $aRet[$item[$filed]] = $item;
            }
        }

        return $aRet;
    }
}