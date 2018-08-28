<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/11
 * Time: 上午12:48
 */

use NoahBuscher\Macaw\Macaw;

Macaw::post('/admin/add', 'SpotController@bAdd');

Macaw::get('/admin/detail', 'SpotController@aGetById');

Macaw::get('/admin/list', 'SpotController@aGetList');

Macaw::get('/admin/update', 'SpotController@bUpdate');

Macaw::post('/journey/add', 'apps\controllers\journey\Journey@bAdd');

Macaw::post('/member/add', 'apps\controllers\member\Member@bAdd');

Macaw::get('/journey/getJourneyList', 'apps\controllers\journey\Journey@aJourneyList');

Macaw::get('/journey/getLeaderIntenion', 'apps\controllers\journey\Journey@aGetLeaderIntention');

Macaw::get('/journey/getJourneyIntention', 'apps\controllers\journey\Journey@aGetJourneyIntention');

Macaw::get('/journey/check', function() {
    echo json_encode([
        'team_name'    => '新兵蛋仔',
        'team_program' => '约局旅行',
        'version'      => '1.0',
    ]);
});


Macaw::get('(:all)', function($fu){
    echo "新兵蛋仔<br>".$fu;
});

Macaw::dispatch();