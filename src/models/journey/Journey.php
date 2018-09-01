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
    protected $fillable = array('spot_id', 'relation', 'status', 'people_num', 'start_time',
        'end_time', 'desc', 'min_budget', 'max_budget', 'uid');
    protected $hidden   = array();

    /**
     * @param $aParam
     * @return mixed
     * @throws \apps\libs\Exception
     */
    public static function bAdd($aParam)
    {
        $oQuery = self::query();

        $oJourney = $oQuery->create($aParam);

        if (null === $oJourney) {
            throw new \apps\libs\Exception('', \apps\libs\Exception::ERR_DB_ERROR);
        }

        return $oJourney->id;
    }

    /**
     * 根据journey_id批量获取旅行信息
     *
     * @param $ids
     * @return array
     */
    public static function aGetJourneyByIds($ids)
    {
        $ids = array_unique($ids);
        $oJourneyQuery = self::query();
        $aJourneyList  = $oJourneyQuery->whereIn('id', $ids)->get();
        if (empty($aJourneyList)) {
            return [];
        }

        return $aJourneyList->toArray();
    }

    /**
     *  初始状态 => 等待成员加入 状态
     *
     * @param $journeyId
     * @return int
     */
    public static function iWaitMember($journeyId)
    {
        $oModel = self::query()->find($journeyId);
        $oModel->status = \apps\common\Constant::JOURNEY_STATUS_JOIN;

        return $oModel->save() ? 1 : 0;
    }

    /**
     * 等待成员加入 => 等待投票
     *
     * @param $journeyId
     * @return int
     */
    public static function iSetMemberFull($journeyId)
    {
        $oModel = self::query()->find($journeyId);
        $oModel->status = \apps\common\Constant::JOURNEY_STATUS_VOTE;
        $oModel->vote_time = date('Y-m-d H:i:s');

        return $oModel->save() ? 1 : 0;
    }

    /**
     * 等待投票 => 成局
     *
     * @param $journeyId
     * @return int
     */
    public static function iSucc($journeyId)
    {
        $oModel = self::query()->find($journeyId);
        $oModel->status = \apps\common\Constant::JOURNEY_STATUS_SUCC;
        $oModel->succ_time = date('Y-m-d H:i:s');

        return $oModel->save() ? 1 : 0;
    }

    /**
     * 流局
     * @param $journeyId
     * @return int
     */
    public static function iFail($journeyId)
    {
        $oModel = self::query()->find($journeyId);
        $oModel->status = \apps\common\Constant::JOURNEY_STATUS_FAIL;

        return $oModel->save() ? 1 : 0;
    }
}