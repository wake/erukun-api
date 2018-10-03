<?php


  /**
   *
   * Team type
   *
   */
  define ('_TEAM_USER_TYPE_LINE',    1);


  /**
   *
   * Wash team to Line group data
   *
   */
  $app['teamsToLine'] = $app->protect (function ($teams) use ($app) {

    $data = [];

    foreach ($teams as $k => $v)
      $data[$k] = $app['teamToLine'] ($v);

    return $data;
  });


  /**
   *
   * Wash team to Line group data
   *
   */
  $app['teamToLine'] = $app->protect (function ($team) use ($app) {

    $data = [];

    if (is_object ($team))
      $team = $team->as_array ();

    $data['id'] = $team['id'];
    $data['groupId'] = $team['line_group_id'];
    $data['groupName'] = $team['line_group_name'];
    $data['groupDesc'] = $team['line_group_desc'];
    $data['createdate'] = $team['createdate'];

    return $data;
  });
