<?php
namespace apps\controllers\admin\spot;

/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/22
 * Time: 上午12:36
 */
class SpotController extends \apps\controllers\BaseController
{
    public function bAdd()
    {
        $aParam = \apps\utils\admin\SpotUtils::aGetParam();

        $oQuery = \apps\models\admin\spot\Spot::query();

        $ret = $oQuery->updateOrCreate($aParam);

        echo json_encode(array(
            'errno' => 0,
            'errmsg' => '',
            'data'   => intval($ret),
        ));
    }

    public function aGetById()
    {
        $web_factory = new \Aura\Web\WebFactory($GLOBALS);
        $request = $web_factory->newRequest();

        $id = $request->query->get('id', 0);

        $oQuery = \apps\models\admin\spot\Spot::query();
        $aRet = $oQuery->find($id);

        echo json_encode(
            array(
                'errno'  => 0,
                'errmsg' => '',
                'data'   => $aRet,
            )
        );
    }

    public function aGetList()
    {
        $web_factory = new \Aura\Web\WebFactory($GLOBALS);
        $request = $web_factory->newRequest();

        $page = $request->query->get('page', 1);
        $size = $request->query->get('size', 10);

        $oQuery = \apps\models\admin\spot\Spot::query();
        $offset = $size * ($page - 1);
        $aRet = $oQuery->offset($offset)->limit($size)->get();

        echo json_encode(
            array(
                'errno'  => 0,
                'errmsg' => '',
                'data'   => $aRet,
            )
        );
    }

    public function bUpdate()
    {
        $web_factory = new \Aura\Web\WebFactory($GLOBALS);
        $request = $web_factory->newRequest();

        $id = $request->query->get('id', 0);
        if (!$id) {
            echo json_encode([
                'errno'  => 1,
                'errmsg' => 'invalid id',
                'data'   => [],
            ]);
        }

        $oQuery = \apps\models\admin\spot\Spot::query();
        $oModel = $oQuery->find($id);
        $oModel->name       = $request->query->get('name', $oModel->name);
        $oModel->reason     = $request->query->get('reason', $oModel->reason);
        $oModel->mddid      = $request->query->get('mddid', $oModel->mddid);
        $oModel->w_id       = $request->query->get('w_id', $oModel->w_id);
        $oModel->min_num    = $request->query->get('min_num', $oModel->min_num);
        $oModel->max_num    = $request->query->get('max_num', $oModel->max_num);
        $oModel->min_time   = $request->query->get('min_time', '');
        $oModel->max_time   = $request->query->get('max_time', '');
        $oModel->is_wrap    = $request->query->get('is_wrap', 0);
        $oModel->relation   = $request->query->get('relation', 0);
        $oModel->min_budget = $request->query->get('min_budget', 0);
        $oModel->max_budget = $request->query->get('max_budget', 0);
        $oModel->spread     = $request->query->get('spread', '');

        $ret = $oModel->save();

        echo json_encode(array(
            'errno' => 0,
            'errmsg' => '',
            'data'   => intval($ret),
        ));
    }
}