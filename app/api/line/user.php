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

  })->bind ('line/users');


  /**
   *
   * Create Line user
   *
   */
  $app->post ('/line/user', function (Request $request) use ($app) {

    // Receive JSON data
    $post = json_decode (file_get_contents ('php://input'), true);

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

  })->bind ('POST:line/user');


  /**
   *
   * Update Line user
   *
   */
  $app->put ('/line/user/{uid}', function (Request $request, $uid) use ($app) {

    // Receive JSON data
    $post = json_decode (file_get_contents ('php://input'), true);

    // Check if team already exist
    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_one ();

    if (! $user)
      return $app['json-error'] (400, 'User not exists');

    // Create user
    $user->line_nick = isset ($post['userName']) ? $post['userName'] : null;
    $user->line_avatar = isset ($post['userAvatar']) ? $post['userAvatar'] : null;;
    $user->save ();

    // Get created user
    $user = Model::Factory ('User')->find_one ($user->id);

    return $app['json-success'] (200, $app['userToLine'] ($user));

  })->bind ('PUT:line/user');
