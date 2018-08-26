<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午5:31
 */

namespace apps\controllers\member;

class Member extends \apps\controllers\BaseController
{
    public function bAdd()
    {
        try {
            $aParam = \apps\utils\member\MemberUtils::getParam();

            \apps\utils\member\MemberUtils::bValid($aParam);

            $oQuery = \apps\models\member\Member::query();
            $ret = $oQuery->create($aParam);

            if (false !== $ret) {
                \apps\libs\BuildReturn::aBuildReturn(['id' => $ret->id]);
            } else {
                \apps\libs\BuildReturn::aBuildReturn([], Exception::ERR_PARAM_ERROR);
            }
        } catch (\Exception $e) {
            \apps\libs\BuildReturn::aBuildReturn([], $e->getCode(), $e->getMessage());
        }
    }
}