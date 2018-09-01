<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/1
 * Time: 下午4:54
 */

namespace apps\models\strategy;


/**
 * @property int journey_id
 * @property int spot_id
 * @property int status
 * @property string create_at
 * @property string update_at
 * Class Strategy
 * @package apps\models\strategy
 */
class Strategy extends \Illuminate\Database\Eloquent\Model
{
    protected $guard    = array('id',);
    protected $fillable = array('journey_id', 'spot_id', 'status');
    protected $hidden   = array();

    public static function iAdd($aParam)
    {
        $iJourney = intval($aParam['journey_id']);
        $aSpotIds = $aParam['spot_ids'];

        $oQuery = self::query()->create();
        // $oQuery->create()
    }
}