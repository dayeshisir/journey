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
        $aParam['busy_time'] = json_encode($aParam['busy_time']);
        $aParam['free_time'] = json_encode($aParam['free_time']);

        $oQuery = self::query();
        $oMember = $oQuery->updateOrCreate($aParam);
        if (null === $oMember) {

            throw new Exception('', Exception::ERR_DB_ERROR);
        }

        return $oMember->id;
    }

    /**
     * 根据用户的id和journey_id获取意向
     *
     * @param $aParam
     * @return array
     */
    public static function aGetDetail($aParam)
    {
        $oQuery = self::query();
        $oMember = $oQuery->where('uid', '=', $aParam['uid'])
            ->where('journey_id', '=', $aParam['journey_id'])->get();

        if (empty($oMember)) {
            return [];
        }

        $aMember = $oMember->toArray();

        if (empty($aMember)) {
            return [];
        }

        $aRet = $aMember[0];
        if (!empty($aRet)) {
            $aRet['busy_time'] = json_decode($aRet['busy_time'], true);
            $aRet['free_time'] = json_decode($aRet['free_time'], true);
        }

        return $aRet;
    }

    /**
     * 获取一个旅行局的所有收集的信息
     *
     * @param $aParam
     * @return array
     */
    public static function aGetJourneyGroup($aParam)
    {
        $oOuery  = self::query();
        $aMember = $oOuery->where('journey_id', '=', $aParam['journey_id'])->get();
        if (empty($aMember)) {
            return [];
        }

        return $aMember->toArray();
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