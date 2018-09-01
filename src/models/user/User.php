<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午10:50
 */

namespace apps\models\user;


use apps\libs\Exception;

/**
 * @property int id
 * @property int uid
 * @property string portrait
 * @property string nick_name
 *
 * Class User
 * @package apps\models\user
 */
class User extends \Illuminate\Database\Eloquent\Model
{
    /**
     * @var array
     */
    protected $guard    = array('id',);
    protected $fillable = array('openid', 'unionid', 'gender', 'portrait', 'nick_name',);
    protected $hidden   = array();

    public static function bAdd($aParam)
    {
        $oQuery = self::query();
        $oUser  = $oQuery->updateOrCreate($aParam);
        if (null === $oUser) {

            throw new Exception('', Exception::ERR_DB_ERROR);
        }

        return $oUser->id;
    }

    /**
     * 根据用户的uid获取用户信息
     *
     * @param $aUids
     * @return array
     */
    public static function aGetUserByIds($aUids)
    {
        $aUids  = array_unique($aUids);
        $oQuery = self::query();
        $aUser  = $oQuery->whereIn('openid', $aUids)->get();
        if (empty($aUser)) {
            return [];
        }

        return $aUser->toArray();
    }
}