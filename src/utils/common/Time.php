<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/1
 * Time: 下午6:58
 */

namespace apps\utils\common;

use League\Period\Period;


class Time
{
    public static function aFindIntersectTime($aPeriod)
    {
        // 第一步，找出这些时间段的最长区间，也就是包含所有时间段的最小时间段
        $oLonggestPeriod = self::aGetMinClosurePeriod($aPeriod);
        $aStats = [];
        foreach ($oLonggestPeriod->getDatePeriod('1 DAY') as $date) {
            // var_dump($date); exit;
            $aStats[$date] = 0;
        }

        foreach ($aPeriod as $aTimeInterval) {
            foreach ($aTimeInterval as $period) {
                $oCurPeriod = new Period($period['start_time'], $period['end_time']);

                foreach ($oCurPeriod->getDatePeriod('1 DAY') as $date) {
                    $aStats[$date]++;
                }
            }
        }

        $iTargetNum = count($aPeriod);
        $aRet = [];
        $oCollectPeriod = null;
        foreach ($aStats as $date => $num) {
            if ($iTargetNum === $num) {
                if (null === $oCollectPeriod) {
                    $oCollectPeriod = Period::createFromDay($date);
                } else {
                    $oCollectPeriod->endingOn($date);
                }
            } else {
                $aRet[] = [
                    'start_time' => $oCollectPeriod->getStartDate(),
                    'end_time'   => $oCollectPeriod->getEndDate(),
                ];
            }
        }

        return $aRet;
    }

    /**
     * 寻找包含所有时间区间的最小闭包
     *
     * @param $aPeriod
     * @return Period|null
     */
    public static function aGetMinClosurePeriod($aPeriod)
    {
        $oLongestPeriod = null;
        if (empty($aPeriod)) {
            return null;
        }

        foreach ($aPeriod as $aTimeInterval) {
            foreach ($aTimeInterval as $period) {
                $oCurPeriod  = new Period($period['start_time'], $period['end_time']);

                if (null === $oLongestPeriod) {
                    $oLongestPeriod = $oCurPeriod;

                    continue;
                }

                if ($oCurPeriod->getStartDate() < $oLongestPeriod->getStartDate()) {
                    $oLongestPeriod = $oLongestPeriod->startingOn($oCurPeriod->getStartDate());
                }
                if ($oCurPeriod->getEndDate() > $oLongestPeriod->getEndDate()) {
                    $oLongestPeriod = $oLongestPeriod->endingOn($oCurPeriod->getEndDate());
                }
            }
        }

        return $oLongestPeriod;
    }
}