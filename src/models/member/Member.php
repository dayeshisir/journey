<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午5:33
 */

namespace apps\models\member;
use apps\libs\Exception;

/**
 * @property int id
 * @property int journey_id
 * @property int uid
 * @property int type
 * Class Member
 * @package apps\model\member
 */
class Member  extends \Illuminate\Database\Eloquent\Model
{

    protected $guard    = array('id',);
    protected $fillable = array('journey_id', 'uid', 'type', 'intention', 'free_time', 'busy_time');
    protected $hidden   = array();

    public static function bAdd($aParam)
    {
        $oQuery = self::query();
        $oMember = $oQuery->updateOrCreate($aParam);
        if (null === $oMember) {

            throw new Exception('', Exception::ERR_DB_ERROR);
        }

        return $oMember->id;
    }

    /**
     * 获取用户的旅行列表
     *
     * @param $aParam
     * @return array
     */
    public static function aGetUserList($aParam)
    {
        $oQuery = self::query();
        $iOffset = $aParam['size'] * ($aParam['page'] - 1);
        $iLimit  = $aParam['size'];
        $aList = $oQuery->offset($iOffset)->limit($iLimit)->where('uid', '=', $aParam['uid'])->get();
        if (empty($aList)) {
            return [];
        }

        return $aList->toArray();
    }
}