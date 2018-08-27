<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午1:38
 */

namespace apps\controllers\journey;

use apps\libs\Exception;
use apps\libs\Log;

class Journey extends \apps\controllers\BaseController
{
    /**
     * 发起一次活动
     *
     */
    public function bAdd()
    {
        try {
            $aParam = \apps\utils\journey\JourneyUtils::getParam();

            \apps\utils\journey\JourneyUtils::bValid($aParam);

            $iInsertId = \apps\models\journey\Journey::bAdd($aParam);

            \apps\libs\BuildReturn::aBuildReturn(['id' => $iInsertId]);

        } catch (\Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Journey::add fail', ['param' => $aParam, 'errno' => $errno, 'msg' => $errmsg]);
            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }


}