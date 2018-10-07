<?php

  use Silex\Application;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\Response;
  use Symfony\Component\HttpKernel\HttpKernelInterface;
  use Pager\Wrapper\Paris\Pager;
  use Carbon\Carbon;


  /**
   *
   * Get Line groups
   *
   */
  $app->get ('/line/groups', function (Request $request) use ($app) {

    $teams = Model::Factory ('Team')
      ->where_not_null ('line_group_id')
      ->find_array ();

    return $app['json-success'] (200, $app['teamsToLine'] ($teams));

  })->bind ('line/group/list');


  /**
   *
   * Get Line group
   *
   */
  $app->get ('/line/group/{gid}', function (Request $request, $gid) use ($app) {

    // Check if team exist
    $team = Model::Factory ('Team')
      ->where ('line_group_id', $gid)
      ->find_one ();

    if (! $team)
      return $app['json-error'] (400, 'Group not exists');

    return $app['json-success'] (200, $app['teamToLine'] ($team));

  })->bind ('line/group/view');


  /**
   *
   * Create Line group
   *
   */
  $app->post ('/line/group', function (Request $request) use ($app) {

    // Receive JSON data
    $post = json_decode (file_get_contents ('php://input'), true);

    // Invalid input
    if (! is_array ($post))
      return $app['json-error'] (400, 'Invalid input');

    // Invalid groupId
    if (! isset ($post['groupId']) || $post['groupId'] == '')
      return $app['json-error'] (400, 'Invalid group id');

    // Check if team exist
    $team = Model::Factory ('Team')
      ->where ('line_group_id', $post['groupId'])
      ->find_one ();

    if ($team)
      return $app['json-success'] (200, $app['teamToLine'] ($team));

    // Create team
    $team = Model::Factory ('Team')->create ();
    $team->line_group_id = $post['groupId'];
    $team->line_group_name = isset ($post['groupName']) ? $post['groupName'] : null;
    $team->line_group_desc = isset ($post['groupDesc']) ? $post['groupDesc'] : null;
    $team->save ();

    // Reload created team
    $team = Model::Factory ('Team')->find_one ($team->id);

    return $app['json-success'] (200, $app['teamToLine'] ($team));

  })->bind ('line/group/create');


  /**
   *
   * Update Line group
   *
   */
  $app->put ('/line/group/{gid}', function (Request $request, $gid) use ($app) {

    // Check if team already exist
    $team = Model::Factory ('Team')
      ->where ('line_group_id', $gid)
      ->find_one ();

    if (! $team)
      return $app['json-error'] (400, 'Group not exists');

    // Receive JSON data
    $post = json_decode (file_get_contents ('php://input'), true);

    // Invalid input
    if (! is_array ($post))
      return $app['json-error'] (400, 'Invalid input');

    // Update team
    $team->line_group_name = array_key_exists ('groupName', $post) ? $post['groupName'] : $team->line_group_name;
    $team->line_group_desc = array_key_exists ('groupDesc', $post) ? $post['groupDesc'] : $team->line_group_desc;
    $team->save ();

    // Reload created team
    $team = Model::Factory ('Team')->find_one ($team->id);

    return $app['json-success'] (200, $app['teamToLine'] ($team));

  })->bind ('line/group/update');


  /**
   *
   * Delete Line group
   *
   */
  $app->delete ('/line/group/{gid}', function (Request $request, $gid) use ($app) {

    // Check if group exist
    $team = Model::Factory ('Team')
      ->where ('line_group_id', $gid)
      ->find_one ();

    if (! $team)
      return $app['json-error'] (400, 'Group not exists');

    // Delete all issues in the team
    $issues = Model::Factory ('Issue')
      ->where ('team_id', $team->id)
      ->find_many ();

    foreach ($issues as $issue)
      $issue->delete ();

    // Remove all user relation of the team
    $rels = Model::Factory ('TeamUser')
      ->where ('team_id', $team->id)
      ->find_many ();

    foreach ($rels as $rel)
      $rel->delete ();

    // Delete team
    $team->delete ();

    return $app['json-success'] (200, null);

  })->bind ('line/group/delete');
