<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午10:49
 */

namespace apps\controllers\user;


class User extends \apps\controllers\BaseController
{
    /**
     * 更新或者新增一个用户
     *
     */
    public function bAdd()
    {
        $iUid      = \apps\libs\Request::mGetCookie('uid', 0);
        $sPortrait = \apps\libs\Request::mGetCookie('portrait', '');
        $sNickName = \apps\libs\Request::mGetCookie('nick_name', '');

        try {
            if (empty($iUid) || $iUid <= 0 || empty($sPortrait) || empty($sNickName)) {
                throw new \apps\libs\Exception('',\apps\libs\Exception::ERR_PARAM_ERROR);
            }

            $aParam = [
                'uid'       => $iUid,
                'portrait'  => $sPortrait,
                'nick_name' => $sNickName,
            ];
            $oQuery = \apps\models\user\User::query();
            $oUser  = $oQuery->updateOrCreate($aParam);
            if (null === $oUser) {
                throw new \apps\libs\Exception('', \apps\libs\Exception::ERR_DB_ERROR);
            }

            \apps\libs\BuildReturn::aBuildReturn(['id' => $oUser->id]);
        } catch (\Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            \apps\libs\Log::vWarning('Member::add fail', ['param' => $aParam, 'errno' => $errno, 'msg' => $errmsg]);

            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }
}