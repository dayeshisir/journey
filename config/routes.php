<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/11
 * Time: 上午12:48
 */

use NoahBuscher\Macaw\Macaw;

// 用户获取openid
Macaw::get('/user/openid', 'apps\controllers\user\User@aGetOpenId');

// 发起旅局
Macaw::post('/journey/add', 'apps\controllers\journey\Journey@bAdd');

// 报名，也即是入局
Macaw::post('/member/add', 'apps\controllers\member\Member@bAdd');

// 开始收集用户意向
Macaw::get('/journey/prepareJoin', 'apps\controllers\journey\Journey@aPrepareJoin');

// 我的旅局
Macaw::get('/journey/getJourneyList', 'apps\controllers\journey\Journey@aJourneyList');

// 局的详情页 -- 组局
Macaw::get('/journey/getJoinJourney', 'apps\controllers\journey\Journey@aGetJoinJourney');

// 提前组局成功
Macaw::post('/journey/setMemberFull', 'apps\controllers\journey\Journey@iSetMemberFull');

// 策略的换一换
Macaw::get('/journey/getSpot', 'apps\controllers\journey\Journey@aGetSpot');

// 确认成局
Macaw::post('/journey/setJourneySucc', 'apps\controllers\journey\Journey@aSetJourneySucc');

// 局的详情页 -- 投票
Macaw::get('/journey/getVoteJourney', 'apps\controllers\journey\Journey@aGetVoteJourney');

// 投票
Macaw::post('/spot/vote', 'apps\controllers\vote\Vote@aVote');

// 投票列表
Macaw::get('/spot/voteList', 'apps\controllers\vote\Vote@aVoteList');

Macaw::get('/spot/list', 'apps\controllers\spot\Spot@aGetList');

Macaw::post('/push/add', 'apps\controllers\push\Push@aAddPush');

Macaw::post('/push/consume', 'apps\controllers\push\Push@aConsumePush');

Macaw::get('/strategy/addTest', 'apps\controllers\strategy\Strategy@aAddTest');

Macaw::get('/strategy/getTest', 'apps\controllers\strategy\Strategy@aGetTest');

// 保存forum_id，以供发送微信通知
Macaw::post('/member/saveForumId', 'apps\controllers\member\Member@aSaveForumId');

// 旅局的状态，包括局的状态、用户的状态、用户的身份，队长还是队员
Macaw::get('/member/status', 'apps\controllers\member\Member@aUserStatus');

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