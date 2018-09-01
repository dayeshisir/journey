<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/1
 * Time: 上午12:32
 */

namespace apps\models\spot;


/**
 * @property int id
 * @property int mddid
 * @property string name
 * @property string reason
 * @property int w_id
 * @property int min_num
 * @property int max_num
 * @property string min_time
 * @property string max_time
 * @property int is_wrap
 * @property int relation
 * @property int min_budget
 * @property int max_budget
 * @property string spread
 * @property string created_at
 * @property string updated_at
 * Class Spot
 */
class Spot extends \Illuminate\Database\Eloquent\Model
{
    // public $timestamps  = false;
    protected $guard    = array('id',);
    protected $fillable = array('w_id', 'nick_name', 'pic', 'desc', 'mddid', 'mdd_name', 'label', 'min_num', 'max_num',
        'min_days', 'max_days', 'time', 'relation', 'min_budget', 'max_budget', 'min_sbudget', 'max_sbudget', 'priority', 'spread');
    protected $hidden   = array();

    /**
     * 根据条件，挑选出适合的景点
     * @param $aCondtion
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function aGetSpotsByCondition($aCondtion)
    {
        $oQuery = self::query();
        if (!isset($aCondtion['intention'])) {
            $oQuery->where('lable', '=', $aCondtion['intention']);
        }
        $oQuery->where('relation', '=', $aCondtion['relation']);
        $oQuery->where('min_num', '<=', $aCondtion['people_num']);
        $oQuery->where('max_num', '>=', $aCondtion['people_num']);
        $oQuery->where('min_budget', '<=', $aCondtion['min_budget']);
        $oQuery->where('max_budget', '>=', $aCondtion['max_budget']);

        $aRet = $oQuery->get();

        return $aRet;
    }

    public static function aGetList($page, $size)
    {
        $oQuery = self::query();
        $iOffset = ($page - 1) * $size;
        $oQuery->offset($iOffset)->limit($size);

        $aList = $oQuery->get();
        if (empty($aList)) {
            return [];
        }

        $aRet = [];
        foreach ($aList as $spot) {
            $spot['pic'] = json_decode($spot['pic'], true);
            $spot['time'] = json_decode($spot['time'], true);

            $aRet[] = $spot;
        }

        return $aRet;
    }
}