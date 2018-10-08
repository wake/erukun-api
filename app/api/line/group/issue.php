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

    // Check if team exist
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

    // Check if team exist
    $team = Model::Factory ('Team')
      ->where ('line_group_id', $gid)
      ->where_not_null ('line_group_id')
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

    // Invalid input
    if (! is_array ($post))
      return $app['json-error'] (400, 'Invalid input');

    // Invalid userId
    if (! isset ($post['openerId']) || $post['openerId'] == '')
      return $app['json-error'] (400, 'Invalid opener id');

    // Check if opener exists
    $opener = Model::Factory ('User')
      ->where ('line_id', $post['openerId'])
      ->where_not_null ('line_id')
      ->find_one ();

    if (! $opener)
      return $app['json-error'] (400, 'Opener not exists');

    // Check if team-user relation exists
    $rel = Model::Factory ('TeamUser')
      ->where ('team_id', $team->id)
      ->where ('user_id', $opener->id)
      ->find_one ();

    if (! $rel)
      return $app['json-error'] (400, 'Opener not belong to this group');

    $assignee = null;

    // Has userId
    if (isset ($post['assigneeId']) && $post['assigneeId'] != '') {

      // Check if user exists
      $assignee = Model::Factory ('User')
        ->where ('line_id', $post['assigneeId'])
        ->where_not_null ('line_id')
        ->find_one ();

      if (! $assignee)
        return $app['json-error'] (400, 'Assignee not exists');

      // Check if team-user relation exists
      $rel = Model::Factory ('TeamUser')
        ->where ('team_id', $team->id)
        ->where ('user_id', $assignee->id)
        ->find_one ();

      if (! $rel)
        return $app['json-error'] (400, 'Assignee not belong to this group');
    }

    // Issue
    if (! isset ($post['title']) || $post['title'] == '')
      return $app['json-error'] (400, 'Issue `title` is required and can\'t be empty');

    $issue = Model::Factory ('Issue')->create ();
    $issue->team_id = $team->id;
    $issue->opener_id = $opener->id;
    $issue->assignee_id = $assignee ? $assignee->id : null;
    $issue->title = $post['title'];
    $issue->desc = isset ($post['desc']) ? $post['desc'] : null;
    $issue->status  = _ISSUE_STATUS_UNCHECK;
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

    // Check if issue exists
    $issue = Model::Factory ('Issue')
      ->where ('id', $iid)
      ->where ('team_id', $team->id)
      ->find_one ();

    if (! $issue)
      return $app['json-error'] (400, 'Issue not exists');

    // Receive JSON data
    $post = json_decode (file_get_contents ('php://input'), true);

    $assignee = null;

    // Has userId
    if (isset ($post['assigneeId']) && $post['assigneeId'] != '') {

      // Check if user exists
      $assignee = Model::Factory ('User')
        ->where ('line_id', $post['assigneeId'])
        ->where_not_null ('line_id')
        ->find_one ();

      if (! $assignee)
        return $app['json-error'] (400, 'Assignee not exists');

      // Check if team-user relation exists
      $rel = Model::Factory ('TeamUser')
        ->where ('team_id', $team->id)
        ->where ('user_id', $assignee->id)
        ->find_one ();

      if (! $rel)
        return $app['json-error'] (400, 'Assignee not belong to this group');

      $issue->assignee_id = $assignee->id;
    }

    // Null to remove user
    else if (array_key_exists ('assigneeId', $post) && is_null ($post['assigneeId']))
      $issue->assignee_id = null;

    if (array_key_exists ('title', $post)) {

      // Issue title
      if (is_null ($post['title']) || $post['title'] == '')
        return $app['json-error'] (400, 'Issue title can\'t be empty');

      $issue->title = $post['title'];
    }

    if (isset ($post['title']) && $post['title'] == '')
      return $app['json-error'] (400, 'Issue `title` can\'t be empty');

    $issue->desc = array_key_exists ('desc', $post) ? $post['desc'] : $issue->desc;
    $issue->duedate = array_key_exists ('duedate', $post) ? $post['duedate'] : $issue->duedate;
    $issue->status = (isset ($post['status']) && in_array ($post['status'], [_ISSUE_STATUS_UNCHECK, _ISSUE_STATUS_CHECKED])) ? $post['status'] : $issue->status;
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

    return $app['json-success'] (200, $app['issueToLine'] ($issue));

  })->bind ('line/group/issue/uncheck');


  /**
   *
   * Delete Line group issue
   *
   */
  $app->delete ('/line/group/{gid}/issue/{iid}', function (Request $request, $gid, $iid) use ($app) {

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

    $issue->delete ();

    return $app['json-success'] (200, null);

  })->bind ('line/group/issue/delete');
