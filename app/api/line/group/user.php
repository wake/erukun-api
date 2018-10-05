<?php

  use Silex\Application;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\Response;
  use Symfony\Component\HttpKernel\HttpKernelInterface;
  use Pager\Wrapper\Paris\Pager;
  use Carbon\Carbon;


  /**
   *
   * Get Line group users
   *
   */
  $app->get ('/line/group/{gid}/users', function (Request $request, $gid) use ($app) {

    $team = Model::Factory ('Team')
      ->where ('line_group_id', $gid)
      ->where_not_null ('line_group_id')
      ->find_array ();

    if (count ($team) <= 0)
      return $app['json-error'] (400, 'Group not exists');

    $team = $team[0];

    $rels = Model::Factory ('TeamUser')
      ->where ('team_id', $team['id'])
      ->find_array ();

    $uids = dig ($rels, 'user_id');

    $users = Model::Factory ('User')
      ->where_in ('id', $uids + [0])
      ->where_not_null ('line_id')
      ->find_array ();

    return $app['json-success'] (200, $app['usersToLine'] ($users));

  })->bind ('line/group/user/list');


  /**
   *
   * Get Line group user
   *
   */
  $app->get ('/line/group/{gid}/user/{uid}', function (Request $request, $gid, $uid) use ($app) {

    $team = Model::Factory ('Team')
      ->where ('line_group_id', $gid)
      ->where_not_null ('line_group_id')
      ->find_array ();

    if (count ($team) <= 0)
      return $app['json-error'] (400, 'Group not exists');

    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_array ();

    if (count ($user) <= 0)
      return $app['json-error'] (400, 'User not exists');

    $team = $team[0];
    $user = $user[0];

    $rels = Model::Factory ('TeamUser')
      ->where ('team_id', $team['id'])
      ->where ('user_id', $user['id'])
      ->find_array ();

    if (count ($rels) <= 0)
      return $app['json-error'] (400, 'User not exists');

    return $app['json-success'] (200, $app['userToLine'] ($user));

  })->bind ('line/group/user/view');


  /**
   *
   * Create Line group user
   *
   */
  $app->post ('/line/group/{gid}/user', function (Request $request, $gid) use ($app) {

    // Check if team exists
    $team = Model::Factory ('Team')
      ->where ('line_group_id', $gid)
      ->where_not_null ('line_group_id')
      ->find_one ();

    if (! $team)
      return $app['json-error'] (400, 'Group not exists');

    // Receive JSON data
    $post = json_decode (file_get_contents ('php://input'), true);

    // Invalid userId
    if (! isset ($post['userId']) || $post['userId'] == '')
      return $app['json-error'] (400, 'Invalid user id');

    // Check if user exists
    $user = Model::Factory ('User')
      ->where ('line_id', $post['userId'])
      ->where_not_null ('line_id')
      ->find_one ();

    if (! $user) {
      $user = Model::Factory ('User')->create ();
      $user->line_id = $post['userId'];
      $user->save ();

      $user = Model::Factory ('User')->find_one ($user->id);
    }

    // 做新聯繫的同時會更新 Name + Avatar
    if (isset ($post['userName']) && $post['userName'] != '')
      $user->line_nick = $post['userName'];

    if (isset ($post['userAvatar']) && $post['userAvatar'] != '')
      $user->line_avatar = $post['userAvatar'];

    $user->save ();

    // Check if team-user relation exists
    $rel = Model::Factory ('TeamUser')
      ->where ('team_id', $team->id)
      ->where ('user_id', $user->id)
      ->find_one ();

    if (! $rel) {
      $rel = Model::Factory ('TeamUser')->create ();
      $rel->type    = _TEAM_USER_TYPE_LINE;
      $rel->team_id = $team->id;
      $rel->user_id = $user->id;
      $rel->save ();
    }

    return $app['json-success'] (200, $app['userToLine'] ($user));

  })->bind ('line/group/user/create');


  /**
   *
   * Update Line group users
   *
   */
  $app->put ('/line/group/{gid}/user/{uid}', function (Request $request, $gid, $uid) use ($app) {

    // Check if team exists
    $team = Model::Factory ('Team')
      ->where ('line_group_id', $gid)
      ->where_not_null ('line_group_id')
      ->find_one ();

    if (! $team)
      return $app['json-error'] (400, 'Group not exists');

    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_one ();

    if (! $user)
      return $app['json-error'] (400, 'User not exists');

    $rel = Model::Factory ('TeamUser')
      ->where ('team_id', $team->id)
      ->where ('user_id', $user->id)
      ->find_one ();

    if (! $rel)
      return $app['json-error'] (400, 'User not exists');

    // Receive JSON data
    $post = json_decode (file_get_contents ('php://input'), true);

    $user->line_nick = isset ($post['userName']) ? $post['userName'] : null;
    $user->line_avatar = isset ($post['userAvatar']) ? $post['userAvatar'] : null;;
    $user->save ();

    return $app['json-success'] (200, $app['userToLine'] ($user));

  })->bind ('line/group/user/update');


  /**
   *
   * Delete Line group user
   *
   */
  $app->delete ('/line/group/{gid}/user/{uid}', function (Request $request, $gid, $uid) use ($app) {

    // Check if team exists
    $team = Model::Factory ('Team')
      ->where ('line_group_id', $gid)
      ->where_not_null ('line_group_id')
      ->find_one ();

    if (! $team)
      return $app['json-error'] (400, 'Group not exists');

    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_one ();

    if (! $user)
      return $app['json-error'] (400, 'User not exists');

    $rel = Model::Factory ('TeamUser')
      ->where ('team_id', $team->id)
      ->where ('user_id', $user->id)
      ->find_one ();

    if (! $rel)
      return $app['json-error'] (400, 'User not exists');

    // Delete all issues opened by this user
    $issues = Model::Factory ('Issue')
      ->where ('team_id', $team->id)
      ->where ('opener_id', $user->id)
      ->find_many ();

    foreach ($issues as $issue)
      $issue->delete ();

    // Clear all issues assigned to this user
    $issues = Model::Factory ('Issue')
      ->where ('team_id', $team->id)
      ->where ('assignee_id', $user->id)
      ->find_many ();

    foreach ($issues as $issue) {
      $issue->assignee_id = null;
      $issue->delete ();
    }

    // Remove all relation to teams
    $rels = Model::Factory ('TeamUser')
      ->where ('user_id', $user->id)
      ->find_many ();

    foreach ($rels as $rel)
      $rel->delete ();

    return $app['json-success'] (200, null);

  })->bind ('line/group/user/delete');
