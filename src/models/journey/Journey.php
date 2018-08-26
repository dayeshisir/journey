<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午1:08
 */

namespace apps\models\journey;


// class Journey extends \LaravelArdent\Ardent\Ardent
class Journey extends \Illuminate\Database\Eloquent\Model
{
    public static $rules = array(
        'spot_id'    => 'required|numeric',                 // 必要参数，整型
        'relation'   => 'required|between:5,10000',         // 推荐原因
        'intention'  => 'required|numeric|between:0,4',     // 意向
        'status'     => 'required|numeric|betwwen:0,10',
        'people_num' => 'required|numeric|betwwen:1,100',
        'start_time' => 'required|date',
        'end_time'   => 'required|date',
        'desc'       => 'required|between:2,10000'
    );

    protected $guard    = array('id',);
    protected $fillable = array('spot_id', 'relation', 'intention', 'status', 'people_num', 'start_time',
        'end_time', 'desc', 'avg_budget', 'created_uid');
    protected $hidden   = array();
}