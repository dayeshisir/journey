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
        $sStartTime = $oLonggestPeriod->getStartDate()->format('Y-m-d');
        $sEndTime   = $oLonggestPeriod->getEndDate()->format('Y-m-d');
        $iStartDate = strtotime($sStartTime);
        $iEndDate   = strtotime($sEndTime);
        while ($iStartDate <= $iEndDate) {
            $sKey = date('Y-m-d', $iStartDate);

            $aStats[$sKey] = 0;

            $iStartDate += 86400;
        }

        foreach ($aPeriod as $aTimeInterval) {
            foreach ($aTimeInterval as $period) {
                $iStartDate = strtotime($period['start_time']);
                $iEndDate   = strtotime($period['end_time']);

                while ($iStartDate <= $iEndDate) {
                    $sKey = date('Y-m-d', $iStartDate);

                    $aStats[$sKey]++;

                    $iStartDate += 86400;
                }
            }
        }

        $iTargetNum = count($aPeriod);
        $sStartKey  = '';
        $sEndKey    = '';
        $aRet = [];
        foreach ($aStats as $date => $num) {
            if ($iTargetNum == $num) {
                if (empty($sStartKey)) {
                    $sStartKey = $sEndKey = $date;
                } else {
                    $sEndKey = $date;
                }
            } else {
                if (!empty($sStartKey)) {
                    $aRet[] = [
                        'start_time' => $sStartKey,
                        'end_time' => $sEndKey,
                    ];

                    $sStartKey = $sEndKey = '';
                }
            }
        }

        if (!empty($sStartKey)) {
            $aRet[] = [
                'start_time' => $sStartKey,
                'end_time'   => $sEndKey,
            ];
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