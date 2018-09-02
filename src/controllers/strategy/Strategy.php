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
        return \apps\models\spot\Spot::aGetFakeSpots();

        $iJourneyId = \apps\libs\Request::mGetParam('journey_id', 0);

        $aJourneyList = \apps\models\journey\Journey::aGetJourneyByIds([$iJourneyId]);
        $aJourney = current($aJourneyList);

        // 取出所有用户的意向
        $aJurneyIntention = \apps\models\member\Member::aGetJourneyGroup($iJourneyId);

        // 分析得到队员的出游意向
        $aCondition = [
            'intention'  => self::iGetIntention($aJurneyIntention),
            'num'        => $aJourney['people_num'],
            'min_budget' => $aJourney['min_budget'],
            'max_budget' => $aJourney['max_budget'],
        ];

        $aSpots = \apps\models\spot\Spot::aGetSpotsByCondition($aCondition);

        // 根据关系过滤
        $aSpots = self::filterRelation($aSpots, $aJourney['relation']);

        // 过滤时间
        $aSpots = self::filterTime($aSpots, $aJourney, $aJurneyIntention);

        return $aSpots;
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
                        $iValidDays = self::iGetDays($iValidStartTime, $iValidEndTime);
                        // 天数合适，即队员选出的适宜天数满足景点推荐的适宜天数
                        if ($iValidDays >= $spot['min_days'] && $iValidDays <= $spot['max_num']) {
                            $bFit = true;

                            $aFitTimeInterval[] = $interval;
                        }
                    }
                }
            }

            if ($bFit) {
                $aRet[$spot['id']] = [
                    'spot' => $spot,
                    'time' => $aFitTimeInterval,
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

        $iChina       = count($aIntentionMap[\apps\common\Constant::INTENTION_TYPE_CHINA]);
        $iInternation = count($aIntentionMap[\apps\common\Constant::INTENTION_TYPE_INTERNATION]);
        $iAny         = count($aIntentionMap[\apps\common\Constant::INTENTION_TYPE_ANY]);

        $iTotalNum = $iChina + $iInternation + $iAny;
        $fChinaRatio = sprintf("%.2f", $iChina/$iTotalNum);
        $fInternationRatio = sprintf("%.2f", $iInternation/$iTotalNum);
        $iAny = 1 - $fChinaRatio - $fInternationRatio;

        // rule 1 : 【国内】【国外】意向的人数比例都小于25%，则认为这个局没有目的地意向
        if (self::bSmaller($fChinaRatio, self::INTENTION_BASE) && self::bSmaller($fInternationRatio, self::INTENTION_BASE)) {
            return \apps\common\Constant::INTENTION_TYPE_ANY;
        }

        // rule 3 : 【国内】或【国外】任一意向的人数比例超过60%，则认为这个意向为局的意向
        if (!self::bSmaller($fChinaRatio, self::INTENTION_WIN)) {
            return \apps\common\Constant::INTENTION_TYPE_CHINA;
        }

        if (!self::bSmaller($fInternationRatio, self::INTENTION_WIN)) {
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

        if (self::bSmaller($fMaxRatio, self::INTENTION_WIN) && !self::bSmaller($fMinRatio, self::INTENTION_BASE)
            && !self::bSmaller($fMaxRatio - $fMinRatio, self::INTENTION_DIFF)) {
            return $bChina ? \apps\common\Constant::INTENTION_TYPE_CHINA : \apps\common\Constant::INTENTION_TYPE_INTERNATION;
        }

        return \apps\common\Constant::INTENTION_TYPE_ANY;
    }

    /**
     *  op1 小于 op2 返回 true
     * @param $op1
     * @param $op2
     * @return bool
     */
    protected static function bSmaller($op1, $op2)
    {
        return abs($op1 - $op2) < self::INFINITE_NUM;
    }
}