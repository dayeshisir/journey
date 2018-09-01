<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/1
 * Time: 上午9:01
 */

namespace apps\controllers\vote;


use apps\controllers\BaseController;
use apps\libs\Exception;

class Vote extends BaseController
{
    /**
     * 投票
     */
    public function aVote()
    {
        $aParam = [
            'journey_id' => \apps\libs\Request::mGetParam('journey_id', 0),
            'spot_id'    => \apps\libs\Request::mGetParam('spot_id', 0),
            'uid'        => \apps\libs\Request::mGetParam('uid', ''),
            'vote'       => \apps\libs\Request::mGetParam('vote', 0),
        ];
        try {
            $iNow  = time();
            $oVote = \apps\models\vote\Vote::oVote($aParam);
            $iVoteTime = strtotime($oVote->create_at);

            if ($iNow > $iVoteTime) {
                throw new Exception('', Exception::ERR_ALREADY_VOTED);
            }

            \apps\libs\BuildReturn::aBuildReturn(['vote_id' => $oVote->id]);
        } catch (Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Member::add fail', ['param' => $aParam, 'errno' => $errno, 'msg' => $errmsg]);
            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }

    public function aVoteList()
    {
        $iJourneyId = \apps\libs\Request::mGetParam('journey_id', 0);
        $iSpotId    = \apps\libs\Request::mGetParam('spot_id', 0);
        $sUid       = \apps\libs\Request::mGetParam('uid', '');
        try {
            $aVoteList = \apps\models\vote\Vote::aJourneyVote($iJourneyId, $iSpotId);
            $aVoteMap  = \apps\utils\common\Util::array2map($aVoteList, 'uid');
            $aMember   = \apps\models\member\Member::aGetJourneyGroup($iJourneyId);

        } catch (Exception $e) {

        }
    }
}