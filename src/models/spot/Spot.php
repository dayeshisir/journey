<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/1
 * Time: 上午12:32
 */

namespace apps\models\spot;
use apps\libs\Exception;


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
        if (isset($aCondtion['intention']) && $aCondtion['intention'] > 0) {
            $oQuery->where('label', '=', $aCondtion['intention']);
        }
        $oQuery->where('min_num', '<=', $aCondtion['people_num']);
        $oQuery->where('max_num', '>=', $aCondtion['people_num']);
        $oQuery->where('min_budget', '<=', $aCondtion['min_budget']);
        $oQuery->where('max_budget', '>=', $aCondtion['max_budget']);

        $aRet = $oQuery->get();
        if (null === $aRet) {
            return [];
        }

        $aRet = $aRet->toArray();
        foreach ($aRet as $key => $item) {
            $aRet[$key]['time'] = json_decode($item['time'], true);
        }

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

    /**
     * 获取一个景点详情
     *
     * @param $id
     * @return array
     * @throws Exception
     */
    public static function aGetDetail($id)
    {
        $oSpot = self::query()->find($id);
        if (null === $oSpot) {

            throw new Exception(Exception::ERR_PARAM_ERROR);
        }

        $aRet = $oSpot->toArray();
        $aRet['pic']  = json_decode($aRet['pic'], true);
        $aRet['time'] = json_decode($aRet['time'], true);

        return $aRet;
    }

    public static function aGetFakeSpots()
    {
        $oQuery = self::query();
        $oQuery->offset(0)->limit(100);
        $oSpots = $oQuery->get();

        $aSpots = $oSpots->toArray();

        $aSpots = shuffle($aSpots);

        $aRet = [];
        foreach ($aSpots as $spot) {

            $spot['pic'] = json_decode($spot['pic'], true);
            $spot['time'] = json_decode($spot['time'], true);

            $aRet[$spot['id']] = [
                'spot' => $spot,
                'time' => [
                    [
                        'start_time' => '2018-' . $spot['time'][0]['start_time'],
                        'end_time' => '2018-' . $spot['time'][0]['end_time'],
                    ]
                ],
            ];
        }

        return $aRet;
    }
}