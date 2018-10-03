<?php

  use Silex\Application;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\Response;
  use Symfony\Component\HttpKernel\HttpKernelInterface;
  use Pager\Wrapper\Paris\Pager;
  use Carbon\Carbon;


  /**
   *
   * Get Line group
   *
   */
  $app->get ('/line/groups', function (Request $request) use ($app) {

    $teams = Model::Factory ('Team')
      ->where_not_null ('line_group_id')
      ->find_array ();

    return $app['json-success'] (200, $app['teamsToLine'] ($teams));

  })->bind ('line/group');


  /**
   *
   * Create Line group
   *
   */
  $app->post ('/line/group', function (Request $request) use ($app) {

    // Receive JSON data
    $post = json_decode (file_get_contents ('php://input'), true);

    // Invalid groupId
    if (! isset ($post['groupId']) || $post['groupId'] == '')
      return $app['json-error'] (400, 'Invalid group id');

    // Check if team already exist
    $team = Model::Factory ('Team')
      ->where ('line_group_id', $post['groupId'])
      ->find_array ();

    if (count ($team) > 0)
      return $app['json-success'] (200, $app['teamToLine'] ($team[0]));

    // Create team
    $team = Model::Factory ('Team')->create ();
    $team->line_group_id = $post['groupId'];
    $team->line_group_name = isset ($post['groupName']) ? $post['groupName'] : null;
    $team->line_group_desc = isset ($post['groupDesc']) ? $post['groupDesc'] : null;
    $team->save ();

    // Re-get created team
    $team = Model::Factory ('Team')->find_one ($team->id);

    return $app['json-success'] (200, $app['teamToLine'] ($team));

  })->bind ('POST:line/group');


  /**
   *
   * Update Line group
   *
   */
  $app->put ('/line/group/{gid}', function (Request $request, $gid) use ($app) {

    // Receive JSON data
    $post = json_decode (file_get_contents ('php://input'), true);

    // Check if team already exist
    $team = Model::Factory ('Team')
      ->where ('line_group_id', $gid)
      ->find_one ();

    if (! $team)
      return $app['json-error'] (400, 'Group not exists');

    // Create team
    $team->line_group_name = isset ($post['groupName']) ? $post['groupName'] : null;
    $team->line_group_desc = isset ($post['groupDesc']) ? $post['groupDesc'] : null;
    $team->save ();

    // Re-get created team
    $team = Model::Factory ('Team')->find_one ($team->id);

    return $app['json-success'] (200, $app['teamToLine'] ($team));

  })->bind ('PUT:line/group');
