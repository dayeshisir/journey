<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/11
 * Time: 上午12:48
 */

use NoahBuscher\Macaw\Macaw;

Macaw::get('/fuck', function() {
    echo "成功";
});

Macaw::get('/home', 'HomeController@home');

Macaw::get('/test', 'HomeController@test');

Macaw::post('/admin/add', 'SpotController@bAdd');

Macaw::get('/admin/detail', 'SpotController@aGetById');

Macaw::get('/admin/list', 'SpotController@aGetList');

Macaw::get('/admin/update', 'SpotController@bUpdate');

Macaw::post('/journey/add', 'apps\controllers\journey\Journey@bAdd');


Macaw::get('(:all)', function($fu){
    echo "新兵蛋仔<br>".$fu;
});

Macaw::dispatch();