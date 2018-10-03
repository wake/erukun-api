<?php


  /**
   *
   * Wash user to Line group data
   *
   */
  $app['usersToLine'] = $app->protect (function ($users) use ($app) {

    $data = [];

    foreach ($users as $k => $v)
      $data[$k] = $app['userToLine'] ($v);

    return $data;
  });


  /**
   *
   * Wash user to Line group data
   *
   */
  $app['userToLine'] = $app->protect (function ($user) use ($app) {

    $data = [];

    if (is_object ($user))
      $user = $user->as_array ();

    $data['id'] = $user['id'];
    $data['userId'] = $user['line_id'];
    $data['userName'] = $user['line_nick'];
    $data['userAvatar'] = $user['line_avatar'];
    $data['createdate'] = $user['createdate'];

    return $data;
  });
