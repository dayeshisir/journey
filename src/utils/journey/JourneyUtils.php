<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午1:40
 */

namespace apps\utils\journey;

use apps\libs\Exception;
use Respect\Validation\Validator as v;

class JourneyUtils
{
    /**
     * @var array
     */
    protected static $_aMap = [
        'spot_id'     => 0,
        'relation'    => 0,
        'intention'   => 0,
        'status'      => 0,
        'people_num'  => 0,
        'start_time'  => '',
        'end_time'    => '',
        'avg_budget'  => 0,
        'desc'        => '',
        'created_uid' => 0,
    ];

    public static function getParam()
    {
        $aParam   = array();
        foreach (self::$_aMap as $key => $value) {
            $aParam[$key] = \apps\libs\Request::mGetParam($key, $value);
        }

        return $aParam;
    }

    /**
     *     public static $rules = array(
    'spot_id'    => 'required|numeric',                 // 必要参数，整型
    'relation'   => 'required|between:5,10000',         // 推荐原因
    'intention'  => 'required|numeric|between:0,4',     // 意向
    'status'     => 'required|numeric|betwwen:0,10',
    'people_num' => 'required|numeric|betwwen:1,100',
    'start_time' => 'required|date',
    'end_time'   => 'required|date',
    'desc'       => 'required|between:2,10000'
    );
     *
     * @param $aParam
     */
    public static function bValid($aParam)
    {
        // 一开始是没有景点信息的
//        if (false === v::numeric()->validate($aParam['spot_id']) ) {
//            \apps\libs\Log::vWarning('spot invlaid', $aParam);
//
//            throw new Exception('', Exception::ERR_PARAM_ERROR);
//        }

        if (false === v::numeric()->between(0, 10)->validate($aParam['relation'])) {
            \apps\libs\Log::vWarning('spot invlaid', $aParam);

            throw new Exception('', Exception::ERR_PARAM_ERROR);
        }

        if (false === v::numeric()->between(0, 10)->validate($aParam['intention'])) {
            \apps\libs\Log::vWarning('intension invalid', $aParam);


            throw new Exception('', Exception::ERR_PARAM_ERROR);
        }

        if (false === v::numeric()->between(1, 100)->validate($aParam['people_num'])) {
            \apps\libs\Log::vWarning('people_num invalid', $aParam);


            throw new Exception('', Exception::ERR_PARAM_ERROR);
        }

        if (false === v::date()->validate($aParam['start_time'])) {
            \apps\libs\Log::vWarning('start_time invalid', $aParam);


            throw new Exception('', Exception::ERR_PARAM_ERROR);
        }

        if (false === v::date()->validate($aParam['end_time'])) {
            \apps\libs\Log::vWarning('end_time invalid', $aParam);


            throw new Exception('', Exception::ERR_PARAM_ERROR);
        }

        if (false === v::stringType()->validate($aParam['desc'])) {
            \apps\libs\Log::vWarning('desc invalid', $aParam);


            throw new Exception('', Exception::ERR_PARAM_ERROR);
        }

        return true;
    }
}