<?php

  use Silex\Application;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\Response;
  use Symfony\Component\HttpKernel\HttpKernelInterface;
  use Pager\Wrapper\Paris\Pager;
  use Carbon\Carbon;


  /**
   *
   * Get Line group issues
   *
   */
  $app->get ('/line/group/{gid}/issues', function (Request $request, $gid) use ($app) {

    $team = Model::Factory ('Team')
      ->where ('line_group_id', $gid)
      ->where_not_null ('line_group_id')
      ->find_one ();

    if (! $team)
      return $app['json-error'] (400, 'Group not exists');

    $issues = Model::Factory ('Issue')
      ->where ('team_id', $team->id)
      ->find_many ();

    return $app['json-success'] (200, $app['issuesToLine'] ($issues));

  })->bind ('line/group/issue/list');


  /**
   *
   * Get Line group issues
   *
   */
  $app->get ('/line/group/{gid}/issue/{iid}', function (Request $request, $gid, $iid) use ($app) {

    $team = Model::Factory ('Team')
      ->where ('line_group_id', $gid)
      ->where_not_null ('line_group_id')
      ->find_one ();

    if (! $team)
      return $app['json-error'] (400, 'Group not exists');

    // Check if team exists
    $issue = Model::Factory ('Issue')
      ->where ('id', $iid)
      ->where ('team_id', $team->id)
      ->find_one ();

    if (! $issue)
      return $app['json-error'] (400, 'Issue not exists');

    return $app['json-success'] (200, $app['issueToLine'] ($issue));

  })->bind ('line/group/issue/view');


  /**
   *
   * Create Line group issue
   *
   */
  $app->post ('/line/group/{gid}/issue', function (Request $request, $gid) use ($app) {

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
    if (! isset ($post['creatorId']) || $post['creatorId'] == '')
      return $app['json-error'] (400, 'Invalid creator id');

    // Check if creator exists
    $creator = Model::Factory ('User')
      ->where ('line_id', $post['creatorId'])
      ->where_not_null ('line_id')
      ->find_one ();

    if (! $creator)
      return $app['json-error'] (400, 'Creator not exists');

    // Check if team-user relation exists
    $rel = Model::Factory ('TeamUser')
      ->where ('team_id', $team->id)
      ->where ('user_id', $creator->id)
      ->find_one ();

    if (! $rel)
      return $app['json-error'] (400, 'Creator not belong to this group');

    $user = null;

    // Has userId
    if (isset ($post['userId']) && $post['userId'] != '') {

      // Check if user exists
      $user = Model::Factory ('User')
        ->where ('line_id', $post['userId'])
        ->where_not_null ('line_id')
        ->find_one ();

      if (! $user)
        return $app['json-error'] (400, 'User not exists');

      // Check if team-user relation exists
      $rel = Model::Factory ('TeamUser')
        ->where ('team_id', $team->id)
        ->where ('user_id', $user->id)
        ->find_one ();

      if (! $rel)
        return $app['json-error'] (400, 'User not belong to this group');
    }

    // Issue
    if (! isset ($post['title']) || $post['title'] == '')
      return $app['json-error'] (400, 'Issue `title` is required and can\'t be empty');

    $issue = Model::Factory ('Issue')->create ();
    $issue->team_id = $team->id;
    $issue->user_id = $user ? $user->id : null;
    $issue->title   = $post['title'];
    $issue->status  = _ISSUE_STATUS_UNCHECK;
    $issue->creator_id = $creator->id;
    $issue->duedate = isset ($post['duedate']) ? $post['duedate'] : null;
    $issue->save ();

    $issue = Model::Factory ('Issue')->find_one ($issue->id);

    return $app['json-success'] (200, $app['issueToLine'] ($issue));

  })->bind ('line/group/issue/create');


  /**
   *
   * Update Line group issue
   *
   */
  $app->put ('/line/group/{gid}/issue/{iid}', function (Request $request, $gid, $iid) use ($app) {

    // Check if team exists
    $team = Model::Factory ('Team')
      ->where ('line_group_id', $gid)
      ->where_not_null ('line_group_id')
      ->find_one ();

    if (! $team)
      return $app['json-error'] (400, 'Group not exists');

    // Check if team exists
    $issue = Model::Factory ('Issue')
      ->where ('id', $iid)
      ->where ('team_id', $team->id)
      ->find_one ();

    if (! $issue)
      return $app['json-error'] (400, 'Issue not exists');

    // Receive JSON data
    $post = json_decode (file_get_contents ('php://input'), true);

    $user = null;

    // Has userId
    if (isset ($post['userId']) && $post['userId'] != '') {

      // Check if user exists
      $user = Model::Factory ('User')
        ->where ('line_id', $post['userId'])
        ->where_not_null ('line_id')
        ->find_one ();

      if (! $user)
        return $app['json-error'] (400, 'User not exists');

      // Check if team-user relation exists
      $rel = Model::Factory ('TeamUser')
        ->where ('team_id', $team->id)
        ->where ('user_id', $user->id)
        ->find_one ();

      if (! $rel)
        return $app['json-error'] (400, 'User not belong to this group');
    }

    // Issue
    if (isset ($post['title']) && $post['title'] == '')
      return $app['json-error'] (400, 'Issue `title` can\'t be empty');

    $issue->user_id = $user ? $user->id : $issue->user_id;
    $issue->title   = isset ($post['title']) ? $post['title'] : $issue->title;
    $issue->status  = (isset ($post['status']) && in_array ($post['status'], [_ISSUE_STATUS_UNCHECK, _ISSUE_STATUS_CHECKED])) ? $post['status'] : $issue->status;
    $issue->duedate = (isset ($post['duedate']) || is_null ($post['duedate'])) ? $post['duedate'] : $issue->duedate;
    $issue->save ();

    $issue = Model::Factory ('Issue')->find_one ($issue->id);

    return $app['json-success'] (200, $app['issueToLine'] ($issue));

  })->bind ('line/group/issue/update');


  /**
   *
   * Check Line group issue
   *
   */
  $app->put ('/line/group/{gid}/issue/{iid}/check', function (Request $request, $gid, $iid) use ($app) {

    // Check if team exists
    $team = Model::Factory ('Team')
      ->where_not_null ('line_group_id')
      ->where ('line_group_id', $gid)
      ->find_one ();

    if (! $team)
      return $app['json-error'] (400, 'Group not exists');

    // Check if issue exists
    $issue = Model::Factory ('Issue')
      ->where ('id', $iid)
      ->where ('team_id', $team->id)
      ->find_one ();

    if (! $issue)
      return $app['json-error'] (400, 'Issue not exists');

    $issue->status = _ISSUE_STATUS_CHECKED;
    $issue->save ();

    return $app['json-success'] (200, $app['issueToLine'] ($issue));

  })->bind ('line/group/issue/check');


  /**
   *
   * Update Line group issue
   *
   */
  $app->put ('/line/group/{gid}/issue/{iid}/uncheck', function (Request $request, $gid, $iid) use ($app) {

    // Check if team exists
    $team = Model::Factory ('Team')
      ->where_not_null ('line_group_id')
      ->where ('line_group_id', $gid)
      ->find_one ();

    if (! $team)
      return $app['json-error'] (400, 'Group not exists');

    // Check if issue exists
    $issue = Model::Factory ('Issue')
      ->where ('id', $iid)
      ->where ('team_id', $team->id)
      ->find_one ();

    if (! $issue)
      return $app['json-error'] (400, 'Issue not exists');

    $issue->status = _ISSUE_STATUS_UNCHECK;
    $issue->save ();

    $resp = [
      'state' => ['code' => 1, 'message' => ''],
      'result' => $app['issueToLine'] ($issue)
    ];

    return $app['json-success'] (200, $app['issueToLine'] ($issue));

  })->bind ('line/group/issue/uncheck');
