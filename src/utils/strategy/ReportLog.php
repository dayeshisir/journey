<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/3
 * Time: 下午10:01
 */

namespace apps\utils\strategy;

use apps\libs\Log;

class ReportLog
{
    public static function vStartLog($aCondition, $aJourney)
    {
        $sReadIntention = self::sGetIntention($aCondition['intention']);
        $sBudget        = self::sGetBudget($aJourney['min_budget']);

        $aPersonRead = [
            "intention"  => $sReadIntention,
            "people_num" => '本局出行人数为' . $aCondition['people_num'] .  '人',
            "budget"     => $sBudget,
        ];

        Log::vNotice('策略汇总', $aPersonRead);
    }

    public static function vChooseSpot($aSpot)
    {
        foreach ($aSpot as $spot) {
            $sIntention = \apps\common\Constant::INTENTION_TYPE_CHINA === intval($spot['label']) ? "国内" : "国外";
            list($iMinBudget, $iMaxBudget) = self::aGetBudget($spot['budget']);
            $sRelation = self::sGetRelation($spot['relation']);

            $sLine = sprintf("策略id：%d 意向：%s 人数：[%d, %d] 预算 [%d, %d] 适合的关系：%s",
                $spot['id'], $sIntention, $spot['min_num'], $spot['max_num'], $iMinBudget, $iMaxBudget, $sRelation);

            Log::vNotice($sLine);
        }
    }

    public static function sGetRelation($iRelation)
    {
        $aRelation = [];

        if ($iRelation & 1) {
            $aRelation[] = '朋友';
        }

        if ($iRelation & 2) {
            $aRelation[] = '情侣';
        }

        if ($iRelation & 4) {
            $aRelation[] = '家庭';
        }

        if ($iRelation & 8) {
            $aRelation[] = "同学/同事";
        }

        return implode(',', $aRelation)
    }

    public static function aGetBudget($nos)
    {
        $aNo = explode(',', $nos);
        $min = 100000000;
        $max = 0;
        foreach ($aNo as $no) {
            switch ($no) {
                case 0:
                    break;
                case 1:
                    $min = min($min, 2);
                    $max = max($max, 3000);
                    break;
                case 2:
                    $min = min($min, 3001);
                    $max = max($max, 10000);
                    break;
                case 3:
                    $min = min($min, 10001);
                    $max = max($max, 100000000);
                    break;
                default:
                    break;
            }
        }

        return [$min, $max];
    }

    public static function sGetIntention($intention)
    {
        $sReadIntention = '';
        switch ($intention) {
            case \apps\common\Constant::INTENTION_TYPE_ANY:
                $sReadIntention = "任意位置";
                break;
            case \apps\common\Constant::INTENTION_TYPE_CHINA:
                $sReadIntention = "国内";
                break;
            case \apps\common\Constant::INTENTION_TYPE_INTERNATION:
                $sReadIntention = "国外";
                break;
        }

        return $sReadIntention;
    }

    public static function sGetBudget($budget)
    {
        $sBudget = '';
        switch ($budget) {
            case 0 :
                $sBudget = "预算无所谓";
                break;
            case 1:
                $sBudget = '2 ~ 3000';
                break;
            case 2:
                $sBudget = '3001 ~ 10000';
                break;
            default:
                $sBudget = '10000+';
        }

        return $sBudget;
    }
}