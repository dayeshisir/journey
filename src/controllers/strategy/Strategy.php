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

    public function aPickOne()
    {
        $iJourneyId = \apps\libs\Request::mGetParam('journey_id', 0);

        $aJourneyList = \apps\models\journey\Journey::aGetJourneyByIds([$iJourneyId]);
        $aJourney = current($aJourneyList);

        // 取出所有用户的意向
        $aJurneyIntention = \apps\models\member\Member::aGetJourneyGroup($iJourneyId);

        // 分析得到队员的出游意向
        $aCondition = [
            'intention'  => $this->iGetIntention($aJurneyIntention),
            'relation'   => $aJourney['relation'],
            'num'        => $aJourney['people_num'],
            'min_budget' => $aJourney['min_budget'],
            'max_budget' => $aJourney['max_budget'],
        ];

        $aSpots = \apps\models\spot\Spot::aGetSpotsByCondition($aCondition);

        // 过滤时间
        $aSpots = $this->filterTime($aSpots, $aJourney, $aJurneyIntention);

        return empty($aSpots) ? [] : $aSpots[0];
    }

    protected function filterTime($aSpots, $aJouney, $aIntention)
    {
        // 首先，计算出队员选的时间的交集
        $ret = [];
        $oWholePeriod = new Period($aJouney['start_time'], $aJouney['end_time']);
        try {
            foreach ($aIntention as $intention) {
                foreach ($intention as $time) {
                    $oCurPeriod = new Period($time['start_time'], $time['end_time']);

                    $oWholePeriod = $oWholePeriod->intersect($oCurPeriod);

                }
            }

            foreach ($aSpots as $spot) {
                foreach ($spot['time'] as $time) {
                    $oCurPeriod = new Period($time['start_time'], $time['end_time']);

                    if ($oWholePeriod->intersect($oCurPeriod)) {
                        array_push($ret, $spot);
                        break;
                    }
                }
            }
        } catch (Exception $e) {

            return [];
        }

        return $ret;
    }

    /**
     * 计算团队的意向
     * @param $aIntention
     * @return int
     */
    protected function iGetIntention($aIntention)
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
        if ($this->bSmaller($fChinaRatio, self::INTENTION_BASE) && $this->bSmaller($fInternationRatio, self::INTENTION_BASE)) {
            return \apps\common\Constant::INTENTION_TYPE_ANY;
        }

        // rule 3 : 【国内】或【国外】任一意向的人数比例超过60%，则认为这个意向为局的意向
        if (!$this->bSmaller($fChinaRatio, self::INTENTION_WIN)) {
            return \apps\common\Constant::INTENTION_TYPE_CHINA;
        }

        if (!$this->bSmaller($fInternationRatio, self::INTENTION_WIN)) {
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

        if ($this->bSmaller($fMaxRatio, self::INTENTION_WIN) && !$this->bSmaller($fMinRatio, self::INTENTION_BASE) && !$this->bSmaller($fMaxRatio - $fMinRatio, self::INTENTION_DIFF)) {
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
    protected function bSmaller($op1, $op2)
    {
        return abs($op1 - $op2) < self::INFINITE_NUM;
    }


}