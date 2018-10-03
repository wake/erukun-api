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

    foreach ($issues as $k => $v)
      $data[$k] = $app['issueToLine'] ($v);

    return $data;
  });


  /**
   *
   * Wash issue to Line group data
   *
   */
  $app['issueToLine'] = $app->protect (function ($issue) use ($app) {

    $data = [];

    $team = $issue->team ()->find_one ();
    $user = $issue->user ()->find_one ();
    $creator = $issue->creator ()->find_one ();

    $data['id'] = $issue->id;
    $data['group'] = $team ? $app['teamToLine'] ($team) : null;
    $data['user'] = $user ? $app['userToLine'] ($user) : null;
    $data['creatorId'] = $creator ? $app['userToLine'] ($creator) : null;
    $data['title'] = $issue->title;
    $data['status'] = $issue->status;
    $data['duedate'] = $issue->duedate;
    $data['createdate'] = $issue->createdate;

    return $data;
  });