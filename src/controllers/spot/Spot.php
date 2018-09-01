<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/1
 * Time: 下午12:10
 */

namespace apps\controllers\spot;


use apps\controllers\BaseController;

class Spot extends BaseController
{
    public function aGetList()
    {
        $iPage = \apps\libs\Request::mGetParam('page', 1);
        $iSize = \apps\libs\Request::mGetParam('size', 10);

        $aRet = \apps\models\spot\Spot::aGetList($iPage, $iSize);

        \apps\libs\BuildReturn::aBuildReturn($aRet);
    }
}