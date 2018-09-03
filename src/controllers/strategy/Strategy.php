<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/31
 * Time: 下午11:48
 */

namespace apps\controllers\strategy;


use apps\libs\Exception;
use League\Period\Period;
use apps\libs\Log;

class Strategy
{
    /**
     * 无穷小，比该值还小，可以认为是0
     */
    const INFINITE_NUM   = 0.00001;

    /**
     * 只有超过这个比率，才算有意向
     */
    const INTENTION_BASE = 0.25;

    /**
     * 只有高于该值，才可以对比
     */
    const INTENTION_WIN = 0.6;

    /**
     * 只有差距大于该值，才算胜出
     */
    const INTENTION_DIFF = 0.2;

    public static function aGetCandidate()
    {
        // return \apps\models\spot\Spot::aGetFakeSpots();

        $iJourneyId = \apps\libs\Request::mGetParam('journey_id', 0);

        $aJourneyList = \apps\models\journey\Journey::aGetJourneyByIds([$iJourneyId]);
        $aJourney = current($aJourneyList);

        // 取出所有用户的意向
        $aJurneyIntention = \apps\models\member\Member::aGetJourneyGroup(['journey_id' => $iJourneyId]);

        // 分析得到队员的出游意向
        $aCondition = [
            'intention'  => self::iGetIntention($aJurneyIntention),
            'people_num' => $aJourney['people_num'],
            'min_budget' => $aJourney['min_budget'],
            'max_budget' => $aJourney['max_budget'],
        ];

        $aUids = array_column($aJurneyIntention, 'uid');
        $aUser = \apps\models\user\User::aGetUserByIds($aUids);

        $aNickName = array_column($aUser, "nick_name");

        // 消息汇总
        \apps\utils\strategy\ReportLog::vStartLog($aCondition, $aJourney);

        Log::vNotice("局成员", $aNickName);

        $aSpots = \apps\models\spot\Spot::aGetSpotsByCondition($aCondition);

        Log::vNotice('[根据意向、人数和预算筛选的策略如下：]', []);

        \apps\utils\strategy\ReportLog::vChooseSpot($aSpots);

        Log::vNotice("意向、人数、预算筛选后： ", ["数量为". count($aSpots)]);

        // 根据关系过滤
        $aSpots = self::filterRelation($aSpots, $aJourney['relation']);

        Log::vNotice('剔除不合适的关系后，筛选的策略如下', []);

        \apps\utils\strategy\ReportLog::vChooseSpot($aSpots);
        Log::vNotice("剔除关系后： ", ["数量为". count($aSpots)]);

        // 过滤时间
        $aSpots = self::filterTime($aSpots, $aJourney, $aJurneyIntention);

        Log::vNotice('提出不合适的时间区间后，筛选的策略如下', []);
        \apps\utils\strategy\ReportLog::vFinalSpot($aSpots);
        Log::vNotice("剔除不合适时间后： ", ["数量为". count($aSpots)]);

        $aSpots = self::aSortByRelation($aSpots);

        return $aSpots;
    }

    protected static function aSortByRelation($aSpot)
    {
        $aRelation = [];
        foreach ($aSpot as $key => $item) {
            $aRelation[$key] = $item['relation'];
        }

        array_multisort($aRelation, SORT_NUMERIC, SORT_ASC, $aSpot);

        $aRet = [];
        foreach ($aSpot as $key => $spot) {
            $iSpotId = $spot['spot']['id'];
            $aRet[$iSpotId] = $spot;
        }

        return $aRet;
    }

    /**
     * 根据关系类型，筛选候选人
     *
     * @param $aSpots
     * @param $iRelation
     * @return array
     */
    protected static function filterRelation($aSpots, $iRelation)
    {
        $aRet = [];
        foreach ($aSpots as $spot) {
            if ($spot['relation'] & $iRelation) {
                $aRet[] = $spot;
            }
        }

        return $aRet;
    }

    /**
     * 根据队员选的时间进行筛选
     *
     * @param $aSpots
     * @param $aJouney
     * @param $aIntention
     * @return array
     */
    protected static function filterTime($aSpots, $aJouney, $aIntention)
    {
        $iJourneyStart = strtotime($aJouney['start_time']);
        $iJourneyEnd   = strtotime($aJouney['end_time']);
        $sYear = date('Y', $iJourneyStart);
        $aRet = [];
        foreach ($aSpots as $spot) {
            foreach ($spot['time'] as $time) {
                $iCurStart = strtotime($sYear . '-' . $time['start_time']);
                $iCurEnd   = strtotime($sYear . '-' . $time['end_time']);

                if ($iCurStart <= $iJourneyStart && $iCurEnd >= $iJourneyEnd) {
                    $iRecommendStart = date('Y-m-d', $iJourneyStart);
                    $iRecommendEnd   = date('Y-m-d', $iJourneyStart + $spot['min_days'] * \apps\common\Constant::INTERVAL_TIME_DAY);
                    $aRet[$spot['id']] = [
                        'spot' => $spot,
                        'time' => ['start_time' => $iRecommendStart, 'end_time' => $iRecommendEnd],
                        'relation' => \apps\utils\journey\JourneyUtils::iGetRelation($spot['relation']),
                    ];
                    break;
                }
            }
        }

        return $aRet;

        $aPeriod = [];
        foreach ($aIntention as $intention) {
            $aPeriod[$intention['uid']] = $intention['free_time'];
        }
        $aPeriod[$aJouney['id']] = [['start_time' => $aJouney['start_time'], 'end_time' => $aJouney['end_time']]];

        $aValidInterval = \apps\utils\common\Time::aFindIntersectTime($aPeriod);

        $aRet = [];
        foreach ($aSpots as $spot) {
            $aTimes = $spot['time'];
            $bFit   = false;
            $aFitTimeInterval = [];
            // 一个景点可能有多个合适出行时间
            foreach ($aTimes as $time) {
                // 队员选出的时间间隔，可能会有多个区间
                foreach ($aValidInterval as $interval) {
                    // 队员的适宜时间
                    $iValidStartTime = strtotime($interval['start_time']);
                    $iValidEndTime   = strtotime($interval['end_time']);

                    // 策略匹配上队员的适宜年份，得到适宜出行时间
                    $sYear           = date('Y', $iValidStartTime);
                    $iCurStart       = strtotime($sYear . '-' . $time['start_time']);
                    $iCurEnd         = strtotime($sYear . '-' . $time['end_time']);

                    // 时间合适，即策略的适宜时间包含队员的适宜时间
                    if ($iCurStart <= $iValidStartTime && $iCurEnd >= $iValidEndTime) {
                        $bFit = true;
//                        $iValidDays = self::iGetDays($iValidStartTime, $iValidEndTime);
//                        // 天数合适，即队员选出的适宜天数满足景点推荐的适宜天数
//                        if ($iValidDays >= $spot['min_days'] && $iValidDays <= $spot['max_num']) {
//                            $bFit = true;
//
//                            $aFitTimeInterval[] = $interval;
//                        }
                    }
                }
            }

            if ($bFit) {
                $aRet[$spot['id']] = [
                    'spot' => $spot,
                    'time' => $aFitTimeInterval,
                    'relation' => \apps\utils\journey\JourneyUtils::iGetRelation($spot['relation']),
                ];
            }
        }

        return $aRet;
    }

    /**
     * 根据时间戳计算天数
     *
     * @param $iStart
     * @param $iEnd
     * @return float|int
     */
    protected static function iGetDays($iStart, $iEnd)
    {
        return ($iEnd - $iStart + \apps\common\Constant::INTERVAL_TIME_DAY)/\apps\common\Constant::INTERVAL_TIME_DAY;
    }

    /**
     * 计算团队的意向
     * @param $aIntention
     * @return int
     */
    protected static function iGetIntention($aIntention)
    {
        if (empty($aIntention)) {
            return 0;
        }

        $aIntentionMap = [];
        foreach ($aIntention as $intention) {
            $iCurIntention = intval($intention['intention']);
            $aIntentionMap[$iCurIntention][] = $intention['uid'];
        }

        $iChina       = isset($aIntention[\apps\common\Constant::INTENTION_TYPE_CHINA]) ? count($aIntentionMap[\apps\common\Constant::INTENTION_TYPE_CHINA]) : 0;
        $iInternation = isset($aIntentionMap[\apps\common\Constant::INTENTION_TYPE_INTERNATION]) ? count($aIntentionMap[\apps\common\Constant::INTENTION_TYPE_INTERNATION]) : 0;
        $iAny         = isset($aIntentionMap[\apps\common\Constant::INTENTION_TYPE_ANY]) ? count($aIntentionMap[\apps\common\Constant::INTENTION_TYPE_ANY]) : 0;

        $iTotalNum         = $iChina + $iInternation + $iAny;
        $fChinaRatio       = sprintf("%.2f", $iChina/$iTotalNum);
        $fInternationRatio = sprintf("%.2f", $iInternation/$iTotalNum);
        $iAnyRatio         = sprintf("%.2f", 1 - $fChinaRatio - $fInternationRatio);

        // rule 1 : 【国内】【国外】意向的人数比例都小于25%，则认为这个局没有目的地意向
        if ($fChinaRatio < self::INTENTION_BASE  && $fInternationRatio < self::INTENTION_BASE ) {
            return \apps\common\Constant::INTENTION_TYPE_ANY;
        }

        // rule 3 : 【国内】或【国外】任一意向的人数比例超过60%，则认为这个意向为局的意向
        if (self::INTENTION_WIN < $fChinaRatio) {
            return \apps\common\Constant::INTENTION_TYPE_CHINA;
        }

        if (self::INTENTION_WIN < $fInternationRatio) {
            return \apps\common\Constant::INTENTION_TYPE_INTERNATION;
        }

        // rule 2 : 国内】或【国外】任一意向的人数比例超过25%，但小于60%，则判断两种意向人数比例的差值
        // 若差值大于20%，则认为多数人的意向为这个局的意向
        // 若差值小于20%，则认为这个局没有意向
        $bChina = true;
        $fMaxRatio = $fChinaRatio;
        $fMinRatio = $fInternationRatio;
        if ($fInternationRatio > $fChinaRatio) {
            $bChina = false;
            $fMaxRatio = $fInternationRatio;
            $fMinRatio = $fChinaRatio;
        }

        if ($fMaxRatio < self::INTENTION_WIN &&  self::INTENTION_BASE < $fMinRatio
            && self::INTENTION_DIFF < $fMaxRatio - $fMinRatio ) {
            return $bChina ? \apps\common\Constant::INTENTION_TYPE_CHINA : \apps\common\Constant::INTENTION_TYPE_INTERNATION;
        }

        return \apps\common\Constant::INTENTION_TYPE_ANY;
    }
}