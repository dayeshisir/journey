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
        'status'      => 0,
        'people_num'  => 0,
        'start_time'  => '',
        'end_time'    => '',
        'min_budget'  => 0,
        'max_budget'  => 0,
        'desc'        => '',
        'uid'         => '',
        'forum_id'    => '',
    ];

    public static function aGetAddParam()
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
    public static function bAddParamValid($aParam)
    {
        if (false === v::numeric()->between(\apps\common\Constant::RELATION_FRIENDS,
                \apps\common\Constant::RELATION_OTHER, true)
                ->validate($aParam['relation'])) {
            \apps\libs\Log::vWarning('relation invlaid', $aParam);

            throw new Exception('', Exception::ERR_PARAM_ERROR);
        }

        if (false === v::numeric()->between(\apps\common\Constant::MIN_PEOPLE_NUM,
                \apps\common\Constant::MAX_PEOPLE_NUM)
                ->validate($aParam['people_num'])) {
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

    /**
     * @param $aMember
     * @param $aVote
     * @param $aUser
     * @return array
     */
    public static function aGetVoteMap($aMember, $aVote, $aUser)
    {
        $aUserMap = \apps\utils\common\Util::array2map($aUser, 'uid');
        $aVoteMap = [
            \apps\common\Config::$aVoteIndex[\apps\common\Constant::VOTE_STATUS_NONE] => [],
            \apps\common\Config::$aVoteIndex[\apps\common\Constant::VOTE_STATUS_OK]   => [],
            \apps\common\Config::$aVoteIndex[\apps\common\Constant::VOTE_STATUS_NO]   => [],
        ];
        $aVotedUid = [];
        foreach ($aVote as $vote) {
            $iVote = $vote['vote'];
            $sUid  = $vote['uid'];
            $aVotedUid[$sUid] = 1;

            $aCurUser = [
                'nick_name' => $aUserMap[$sUid]['nick_name'],
                'portrait'  => $aUserMap[$sUid]['portrait'],
            ];
            $index = \apps\common\Config::$aVoteIndex[$iVote];
            array_push($aVoteMap[$index], $aCurUser);
        }

        // 未投票用户
        foreach ($aMember as $member) {
            $sCurUid = $member['uid'];
            if (isset($aVotedUid[$sCurUid])) {
                continue;
            }

            $index = \apps\common\Config::$aVoteIndex[\apps\common\Constant::VOTE_STATUS_NONE];
            $aCurUser = [
                'nick_name' => $aUserMap[$sCurUid]['nick_name'],
                'portrait'  => $aUserMap[$sCurUid]['portrait'],
            ];
            array_push($aVoteMap[$index], $aCurUser);
        }

        return $aVoteMap;
    }
}