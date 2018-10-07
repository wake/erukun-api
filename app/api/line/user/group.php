<?php

  use Silex\Application;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\Response;
  use Symfony\Component\HttpKernel\HttpKernelInterface;
  use Pager\Wrapper\Paris\Pager;
  use Carbon\Carbon;


  /**
   *
   * Get Line user groups
   *
   */
  $app->get ('/line/user/{uid}/groups', function (Request $request, $uid) use ($app) {

    // Check if user exists
    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_one ();

    if (! $user)
      return $app['json-error'] (400, 'User not exists');

    // Check if relation exists
    $rels = Model::Factory ('TeamUser')
      ->where ('user_id', $user->id)
      ->find_array ();

    if (count ($rels) <= 0)
      return $app['json-success'] (200, []);

    $tids = dig ($rels, 'team_id');

    // Check if team exists
    $teams = Model::Factory ('Team')
      ->where_in ('id', $tids + [0])
      ->where_not_null ('line_group_id')
      ->find_array ();

    if (count ($teams) <= 0)
      return $app['json-success'] (200, []);

    return $app['json-success'] (200, $app['teamsToLine'] ($teams));

  })->bind ('line/user/group/list');


  /**
   *
   * Get Line user group
   *
   */
  $app->get ('/line/user/{uid}/group/{gid}', function (Request $request, $uid, $gid) use ($app) {

    // Check if user exists
    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_one ();

    if (! $user)
      return $app['json-error'] (400, 'User not exists');

    // Check if group exists
    $team = Model::Factory ('Team')
      ->where ('line_group_id', $gid)
      ->where_not_null ('line_group_id')
      ->find_one ();

    if (! $team)
      return $app['json-error'] (400, 'Group not exists');

    // Check if relation exists
    $rel = Model::Factory ('TeamUser')
      ->where ('user_id', $user->id)
      ->where ('team_id', $team->id)
      ->find_one ();

    if (! $rel)
      return $app['json-error'] (400, 'Group not exists');

    return $app['json-success'] (200, $app['teamToLine'] ($team));

  })->bind ('line/user/group/view');
