<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午3:56
 */

namespace apps\libs;


class BuildReturn
{
    public static function aBuildReturn($data = array(), $errno = 0, $errmsg = '')
    {
        $aRet = array(
            'errno'  => $errno,
            'errmsg' => $errmsg,
            'data'   => $data,
        );

        echo json_encode($aRet, JSON_UNESCAPED_UNICODE);
    }
}