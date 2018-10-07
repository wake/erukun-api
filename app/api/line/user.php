<?php

  use Silex\Application;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\Response;
  use Symfony\Component\HttpKernel\HttpKernelInterface;
  use Pager\Wrapper\Paris\Pager;
  use Carbon\Carbon;


  /**
   *
   * Get Line users
   *
   */
  $app->get ('/line/users', function (Request $request) use ($app) {

    $users = Model::Factory ('User')
      ->where_not_null ('line_id')
      ->find_array ();

    return $app['json-success'] (200, $app['usersToLine'] ($users));

  })->bind ('line/user/list');


  /**
   *
   * Get Line user
   *
   */
  $app->get ('/line/user/{uid}', function (Request $request, $uid) use ($app) {

    // Check if user exists
    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_one ();

    if (! $user)
      return $app['json-error'] (400, 'User not exists');

    return $app['json-success'] (200, $app['userToLine'] ($user));

  })->bind ('line/user/view');


  /**
   *
   * Create Line user
   *
   */
  $app->post ('/line/user', function (Request $request) use ($app) {

    // Receive JSON data
    $post = json_decode (file_get_contents ('php://input'), true);

    // Invalid input
    if (! is_array ($post))
      return $app['json-error'] (400, 'Invalid input');

    // Invalid userId
    if (! isset ($post['userId']) || $post['userId'] == '')
      return $app['json-error'] (400, 'Invalid user id');

    // Check if team already exist
    $user = Model::Factory ('User')
      ->where ('line_id', $post['userId'])
      ->find_array ();

    if (count ($user) > 0)
      return $app['json-success'] (200, $app['userToLine'] ($user[0]));

    // Create user
    $user = Model::Factory ('User')->create ();
    $user->line_id = $post['userId'];
    $user->line_nick = isset ($post['userName']) ? $post['userName'] : null;
    $user->line_avatar = isset ($post['userAvatar']) ? $post['userAvatar'] : null;;
    $user->save ();

    // Get created user
    $user = Model::Factory ('User')->find_one ($user->id);

    return $app['json-success'] (200, $app['userToLine'] ($user));

  })->bind ('line/user/create');


  /**
   *
   * Update Line user
   *
   */
  $app->put ('/line/user/{uid}', function (Request $request, $uid) use ($app) {

    // Check if user exist
    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_one ();

    if (! $user)
      return $app['json-error'] (400, 'User not exists');

    // Receive JSON data
    $post = json_decode (file_get_contents ('php://input'), true);

    // Invalid input
    if (! is_array ($post))
      return $app['json-error'] (400, 'Invalid input');

    // Update user
    $user->line_nick = array_key_exists ('userName', $post) ? $post['userName'] : $issue->line_nick;
    $user->line_avatar = array_key_exists ('userAvatar', $post) ? $post['userAvatar'] : $issue->line_avatar;
    $user->save ();

    // Get user
    $user = Model::Factory ('User')->find_one ($user->id);

    return $app['json-success'] (200, $app['userToLine'] ($user));

  })->bind ('line/user/update');


  /**
   *
   * Delete Line user
   *
   */
  $app->delete ('/line/user/{uid}', function (Request $request, $uid) use ($app) {

    // Check if user exist
    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_one ();

    if (! $user)
      return $app['json-error'] (400, 'User not exists');

    // Delete all issues opened by this user
    $issues = Model::Factory ('Issue')
      ->where ('opener_id', $user->id)
      ->find_many ();

    foreach ($issues as $issue)
      $issue->delete ();

    // Clear all issues assigned to this user
    $issues = Model::Factory ('Issue')
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

    // Delete user
    $user->delete ();

    return $app['json-success'] (200, null);

  })->bind ('line/user/delete');
