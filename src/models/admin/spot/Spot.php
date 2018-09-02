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
    protected $fillable = array('w_id', 'nick_name', 'pic', 'desc', 'mddid', 'mdd_name', 'label', 'min_num', 'max_num',
        'min_days', 'max_days', 'time', 'relation', 'min_budget', 'max_budget', 'min_sbudget', 'max_sbudget','budget', 'priority', 'spread');
    protected $hidden   = array();

    public function aGetSpotsByCondiction($aCondition)
    {

    }
}