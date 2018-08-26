<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/11
 * Time: 上午1:34
 */

class HomeController extends BaseController
{
    public function home()
    {
        // Article::first();
        $aArticle = Article::first();

        echo json_encode($aArticle, JSON_UNESCAPED_UNICODE);

        return;

//        require dirname(__FILE__) . "/../views/home.php";
    }

    public function test()
    {
        echo json_encode(['one' => 1, 'two' => 2]);
    }
}