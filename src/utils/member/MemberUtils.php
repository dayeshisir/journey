<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午5:36
 */

namespace apps\utils\member;

use apps\libs\Exception;
use Respect\Validation\Validator as v;

class MemberUtils
{
    /**
     * @var array
     */
    protected static $_aMap = [
        'journey_id' => 0,
        'uid'        => 0,
        'portrait'   => 0,
        'type'       => 0,
        'intention'  => 0,
        'free_time'  => '',
        'busy_time'  => '',
    ];

    public static function aGetParam()
    {
        $aParam   = array();
        foreach (self::$_aMap as $key => $value) {
            $aParam[$key] = \apps\libs\Request::mGetParam($key, $value);
        }

        return $aParam;
    }

    public static function bValid($aParam)
    {
        if (false === v::numeric()->validate($aParam['journey_id'])) {
            \apps\libs\Log::vWarning('jouney_id invlid', $aParam);

            throw new Exception('', Exception::ERR_PARAM_ERROR);
        }

        if (false === v::numeric()->validate($aParam['uid'])) {
            \apps\libs\Log::vWarning('uid invalid', $aParam);

            throw new Exception('', Exception::ERR_PARAM_ERROR);
        }

        if (false === v::create()->validate($aParam['portrait'])) {
            \apps\libs\Log::vWarning('portrait invalid', $aParam);

            throw new Exception('', Exception::ERR_PARAM_ERROR);
        }

        return true;
    }
}