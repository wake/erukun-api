<?php


  /**
   *
   * Issue status
   *
   */
  define ('_ISSUE_STATUS_UNCHECK',   0);
  define ('_ISSUE_STATUS_CHECKED',   1);
  define ('_ISSUE_STATUS_DROPPED',   2);


  /**
   *
   * Wash issue to Line data
   *
   */
  $app['issuesToLine'] = $app->protect (function ($issues) use ($app) {

    $data = [];
    $teams = [];
    $users = [];

    if (count ($issues) > 0) {

      $teams = Model::Factory ('Team')
        ->where_in ('id', dig ($issues, 'team_id') + [0])
        ->find_array ();

      $users = Model::Factory ('User')
        ->where_in ('id', dig ($issues, 'opener_id') + dig ($issues, 'assignee_id') + [0])
        ->find_array ();
    }

    foreach ($issues as $k => $v)
      $data[$k] = $app['issueToLine'] ($v, keyi ($teams, 'id'), keyi ($users, 'id'));

    return $data;
  });


  /**
   *
   * Wash issue to Line group data
   *
   */
  $app['issueToLine'] = $app->protect (function ($issue, $teams = [], $users = []) use ($app) {

    $data = [];

    // 處理 team
    if (! empty ($teams) && isset ($teams[$issue->team_id]))
      $team = $teams[$issue->team_id];
    else
      $team = $issue->team ()->find_one ();

    // 處理 assignee
    if (! empty ($users) && isset ($users[$issue->assignee_id]))
      $assignee = $users[$issue->assignee_id];

    else
      $assignee = $issue->assignee ()->find_one ();

    // 處理 opener
    if (! empty ($users) && isset ($users[$issue->opener_id]))
      $opener = $users[$issue->opener_id];

    else
      $opener = $issue->opener ()->find_one ();

    $data['id'] = $issue->id;
    $data['group'] = $team ? $app['teamToLine'] ($team) : null;
    $data['assignee'] = $assignee ? $app['userToLine'] ($assignee) : null;
    $data['opener'] = $opener ? $app['userToLine'] ($opener) : null;
    $data['title'] = $issue->title;
    $data['desc'] = $issue->desc;
    $data['status'] = $issue->status;
    $data['duedate'] = $issue->duedate;
    $data['createdate'] = $issue->createdate;

    return $data;
  });
