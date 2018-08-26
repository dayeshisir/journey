<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/19
 * Time: 下午9:54
 */
class UserController extends BaseController
{
    public function register()
    {
        $aParam = array_merge($_POST, $_GET);
        $user = new User();
        // $ret = $user->save(["name" => $aParam['name'], "password" => $aParam['password']]);
        $user->name = $aParam['name'];
        $user->password = $aParam['password'];
        $ret = $user->save();

        echo json_encode([
            'errno'  => 0,
            'errmsg' => '',
            'data'   => intval($ret),
        ]);
    }
}