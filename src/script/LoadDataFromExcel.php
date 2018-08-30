<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/29
 * Time: 下午11:10
 */
require_once '../../vendor/phpoffice/phpspreadsheet/src/Bootstrap.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class LoadData
{
    protected static $_sFile = '';

    protected static $_aFieldsMap = array(
        "A" => 'w_id',
        "B" => 'nick_name',
        "C" => 'pic_url',
        "D" => 'desc',
        "E" => 'mdd_name',
        "F" => 'location',
        "G" => 'min_recommend_num',
        "H" => 'max_recommend_num',
        "I" => 'time',
        "J" => 'min_recommend_duration',
        'K' => 'max_recommend_duration',
        'L' => 'relation',
        'M' => 'budget',
    );

    public static function init()
    {
        self::$_sFile =  dirname(__FILE__) . "/../../data/source.xlsx";
    }

    public static function load()
    {
        $oSpreadSheet = IOFactory::load(self::$_sFile);
        if (null === $oSpreadSheet) {
            echo "fail to load file [" . self::$_sFile . "]\n";
            exit;
        }

        $aSheetData = $oSpreadSheet->getActiveSheet()->toArray(null, true, true, true);

        $aData = [];
        if (empty($aSheetData)) {
            return [];
        }

        foreach ($aSheetData as $sheet) {
            $aNew = self::transField($sheet);

            $aRecommendTime   = self::aGetRecommendTime($aNew['time']);
            $aRecommendBudget = self::aGetRecommendBudget($aNew['budget']);
            $iRelation        = self::iGetRecommendRelation($aNew['relation']);

            $aData[] = [
                'w_id'               => $aNew['w_id'],
                'nick_name'          => $aNew['nick_name'],
                'pic_url'            => [$aNew['pic_url']],
                'desc'               => $aNew['desc'],
                'mdd_name'           => $aNew['mdd_name'],
                'location'           => $aNew['location'],
                'min_num'            => $aNew['min_recommend_num'],
                'max_num'            => $aNew['max_recommend_num'],
                'recommend_time'     => $aRecommendTime,
                'min_duration'       => $aNew['min_recommend_num'],
                'max_duration'       => $aNew['max_recommend_num'],
                'recommend_relation' => $iRelation,
                'min_budget'         => $aRecommendBudget['min_budget'],
                'max_budget'         => $aRecommendBudget['max_budget'],
            ];
        }

        return $aData;
    }

    /**
     * 存储数据，返回存储了多少
     *
     * @param $aData
     * @return int
     */
    public static function iSave($aData)
    {
        $iCount = 0;
        foreach ($aData as $data) {
            $oSpot = \apps\models\admin\spot\Spot::query()->updateOrCreate($data);
            if (null !== $oSpot) {
                $iCount++;
            }
        }

        return $iCount;
    }

    /**
     * 解析出预算
     *
     * @param $sBudget
     * @return array
     */
    protected static function aGetRecommendBudget($sBudget)
    {
        list($min_budget, $max_budget) = explode(',', $sBudget);

        return ['min_budget' => $min_budget, 'max_budget' => $max_budget];
    }

    /**
     * 解析出推荐的关系类型，关系类型协商为：
     * 1：朋友 2：情侣 4：家庭 8：同学、同事
     * 表中存的是这几个关系的或
     *
     * @param $sRelation
     * @return int
     */
    protected static function iGetRecommendRelation($sRelation)
    {
        $aRelations = explode(',', $sRelation);

        $iRelation = 0;
        foreach ($aRelations as $relation) {
            $iRelation |= intval($relation);
        }

        return $iRelation;
    }

    /**
     * 解析出时间，Excel中的时间格式协商为
     * 3-1,4-1;5-1,6-1;
     * 即：每段时间间隔以分号分割，在每段时间段内的开始时间和结束时间以逗号分割
     *
     * @param $sTime
     * @return array
     */
    protected static function aGetRecommendTime($sTime)
    {
        $aRet   = [];
        $aTimes = explode(';', $sTime);
        foreach ($aTimes as $time) {
            list($start_time, $end_time) = explode(',', $time);
            $aRet[] = ['start_time' => $start_time, 'end_time' => $end_time];
        }

        return $aRet;
    }

    /**
     * 将Excel中的列与实际含义关联起来
     *
     * @param $sheet
     * @return array
     */
    protected static function transField($sheet)
    {
        $aRet = [];
        foreach (self::$_aFieldsMap as $sOrgField => $sTargetField) {
            $aRet[$sTargetField] = $sheet[$sOrgField];
        }

        return $aRet;
    }
}

LoadData::init();
$aSpot = LoadData::load();
$iRet  = LoadData::iSave($aSpot);

echo "$iRet spots saved\n";
