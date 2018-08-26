<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午5:33
 */

namespace apps\models\member;

/**
 * @property int journey_id
 * @property int uid
 * @property string portrait
 * @property int type
 * Class MemberUtils
 * @package apps\model\member
 */
class Member  extends \Illuminate\Database\Eloquent\Model
{

    protected $guard    = array('id',);
    protected $fillable = array('journey_id', 'uid', 'portrait', 'type', );
    protected $hidden   = array();
}