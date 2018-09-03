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
        $aStats = self::aGetDayInterval($aPeriod);

        // 第二部，找出这些时间中可行的时间区间
        $aRet  = self::aGetContinutityValidDate($aStats, $aPeriod);

        return $aRet;
    }

    public static function aFindInValidTime($aPeriod)
    {
        $aStats = self::aGetDayInterval($aPeriod);

        $aRet = self::aGetDispersedInvalidDate($aStats, $aPeriod);

        return $aRet;
    }

    public static function aGetDispersedInvalidDate($aStats, $aPeriod)
    {
        $iTargetNum = count($aPeriod);
        $aRet       = [];
        foreach ($aStats as $date => $num) {
            if ($num <= $iTargetNum) {
                $aRet[$date] = $iTargetNum - $num;
            }
        }

        $sStart  = '';
        $sEnd    = '';
        $iCurNum = 0;
        $aRes = [];
        foreach ($aRet as $date => $num) {
            if (!$num) {
                if (!empty($sStart)) {
                    $aRes[] = [
                        'start_time' => $sStart,
                        'end_time'   => $sEnd,
                        'num'        => $iCurNum,
                    ];

                    $sStart = $sEnd = '';
                }

                continue;
            }
            if (empty($sStart)) {
                $sStart = $date;
                $sEnd   = $date;
                $iCurNum = $num;
            } else if ($iCurNum == $num) {
                $sEnd = $date;
            } else {
                $aRes[] = array(
                    'start_time' => $sStart,
                    'end_time'   => $sEnd,
                    'num'        => $iCurNum,
                );
                $sStart = $date;
                $sEnd   = $date;
                $iCurNum = $num;
            }
        }

        if (!empty($sStart)) {
            $aRes[] = array(
                'start_time' => $sStart,
                'end_time'   => $sEnd,
                'num'        => $iCurNum,
            );
        }


        return $aRes;
    }

    public static function aGetContinutityValidDate($aStats, $aPeriod)
    {
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

    public static function aGetDayInterval($aPeriod)
    {
        $oLonggestPeriod = self::aGetMinClosurePeriod($aPeriod);
        $aStats = [];
        $sStartTime = $oLonggestPeriod->getStartDate()->format('Y-m-d');
        $sEndTime   = $oLonggestPeriod->getEndDate()->format('Y-m-d');
        $iStartDate = strtotime($sStartTime);
        $iEndDate   = strtotime($sEndTime);
        while ($iStartDate <= $iEndDate) {
            $sKey = date('Y-m-d', $iStartDate);

            $aStats[$sKey] = 0;

            $iStartDate += \apps\common\Constant::INTERVAL_TIME_DAY;
        }

        foreach ($aPeriod as $aTimeInterval) {
            foreach ($aTimeInterval as $period) {
                $iStartDate = strtotime($period['start_time']);
                $iEndDate   = strtotime($period['end_time']);

                while ($iStartDate <= $iEndDate) {
                    $sKey = date('Y-m-d', $iStartDate);

                    $aStats[$sKey]++;

                    $iStartDate += \apps\common\Constant::INTERVAL_TIME_DAY;
                }
            }
        }

        return $aStats;
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