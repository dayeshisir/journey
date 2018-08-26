<?php
namespace apps\models\admin\spot;

/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/22
 * Time: 上午12:35
 */

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
    protected $fillable = array('mddid', 'name', 'reason', 'w_id', 'min_num', 'max_num', 'min_time',
        'max_time', 'is_wrap', 'relation', 'min_budget', 'max_budget', 'spread');
    protected $hidden   = array();
}