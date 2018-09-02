<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/1
 * Time: 上午1:01
 */

namespace apps\models\vote;
use apps\libs\Exception;

/**
 * @property string uid
 * @property int journey_id
 * @property int spot_id
 * @property int vote
 * @property string create_at
 * @property string update_at
 * Class Vote
 * @package apps\models\vote
 */
class Vote extends \Illuminate\Database\Eloquent\Model
{
    protected $guard    = array('id',);
    protected $fillable = array('uid', 'journey_id', 'spot_id', 'vote',);
    protected $hidden   = array();

    /**
     * 投票
     *
     * @param $aParam
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     * @throws Exception
     */
    public static function oVote($aParam)
    {
        $oVote = self::query()->updateOrCreate($aParam);
        if (null === $oVote) {
            throw new Exception('', Exception::ERR_DB_ERROR);
        }

        return $oVote;
    }

    /**
     * 得到一个旅居的投票时间
     *
     * @param $journey_id
     * @param $spot_id
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function aJourneyVote($journey_id, $spot_id)
    {
        $oQuery = self::query()->where('journey_id', '=', $journey_id);
        $oQuery->where('spot_id', '=', $spot_id);

        $oVote = $oQuery->get();
        if (null === $oVote) {
            return [];
        }

        return $oVote->toArray();
    }

    /**
     * 得到某个具体的用户在某个局的投票详情
     *
     * @param $journey
     * @param $spot_id
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function aGetUserVote($journey, $spot_id, $uid)
    {
        $oQuery = self::query()->where('journey_id','=', $journey)
            ->where('uid','=', $uid)->where('spot_id', '=', $spot_id);

        $oVote = $oQuery->get();
        if (null === $oVote) {
            return [];
        }

        return $oVote->toArray();
    }
}