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

    /**
     * 获取微信的appid
     *
     */
    public function aGetOpenId()
    {
        $sCode     = \apps\libs\Request::mGetParam('code', '');
        $aUserInfo = \apps\libs\Request::mGetParam('userInfo', '');

        // \apps\libs\Log::vWarning('user', $aUserInfo);

        $appId = \apps\common\Constant::WX_APP_ID;
        $secret = \apps\common\Constant::WX_APP_SECRENT;
        $sUrl="https://api.weixin.qq.com/sns/jscode2session?appid=$appId&secret=$secret&js_code=$sCode&grant_type=authorization_code";

        $aHeaders = ['Accept' => 'application/json'];
        $oRequest = \Requests::get($sUrl, $aHeaders);

        $aRet = json_decode($oRequest->body, true);

        if (!isset($aRet['session_key'])) {

            \apps\libs\BuildReturn::aBuildReturn([], 'get wx open id fail');

            return [];
        }

        $aParam = [
            'openid' => $aRet['openid'],
            'nick_name' => $aUserInfo['nickName'],
            'portrait'  => $aUserInfo['avatarUrl'],
            'gender'    => $aUserInfo['gender'],
        ];

        $oUser = \apps\models\user\User::query()->updateOrCreate($aParam);
        if (null === $oUser) {

            \apps\libs\Log::vWarning('add user fail', $aParam);
        }

        unset($aRet['session_key']);
        \apps\libs\BuildReturn::aBuildReturn($aRet);
    }
}