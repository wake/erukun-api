<?php

  use Silex\Application;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\Response;
  use Symfony\Component\HttpKernel\HttpKernelInterface;
  use Pager\Wrapper\Paris\Pager;
  use Carbon\Carbon;


  /**
   *
   * Get Line user issues
   *
   */
  $app->get ('/line/user/{uid}/issues', function (Request $request, $uid) use ($app) {

    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_one ();

    if (! $user)
      return $app['json-error'] (400, 'User not exists');

    $issues = Model::Factory ('Issue')
      ->where_any_is ([['opener_id' => $user->id], ['assignee_id' => $user->id]])
      ->find_many ();

    if (count ($issues) <= 0)
      return $app['json-success'] (200, []);

    return $app['json-success'] (200, $app['issuesToLine'] ($issues));

  })->bind ('line/user/issue/list');


  /**
   *
   * Get Line user issue
   *
   */
  $app->get ('/line/user/{uid}/issue/{iid}', function (Request $request, $uid, $iid) use ($app) {

    // Check if user exists
    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_one ();

    if (! $user)
      return $app['json-error'] (400, 'User not exists');

    // Check if issue exists
    $issue = Model::Factory ('Issue')
      ->where ('id', $iid)
      ->find_one ();

    if (! $issue)
      return $app['json-error'] (400, 'Issue not exists');

    if ($issue->opener_id != $user->id && $issue->assignee_id != $user->id)
      return $app['json-error'] (400, 'Invalid operation');

    return $app['json-success'] (200, $app['issueToLine'] ($issue));

  })->bind ('line/user/issue/view');


  /**
   *
   * Update Line user issue
   *
   */
  $app->put ('/line/user/{uid}/issue/{iid}', function (Request $request, $uid, $iid) use ($app) {

    // Check if user exists
    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_one ();

    if (! $user)
      return $app['json-error'] (400, 'User not exists');

    // Check if issue exists
    $issue = Model::Factory ('Issue')
      ->where ('id', $iid)
      ->find_one ();

    if (! $issue)
      return $app['json-error'] (400, 'Issue not exists');

    if ($issue->opener_id != $user->id && $issue->assignee_id != $user->id)
      return $app['json-error'] (400, 'Invalid operation');

    // Receive JSON data
    $post = json_decode (file_get_contents ('php://input'), true);

    if (! is_array ($post))
      return $app['json-error'] (400, 'Invalid input');

    if (isset ($post['title'])) {

      // Issue
      if ($post['title'] == '')
        return $app['json-error'] (400, 'Issue can\'t be empty');

      $issue->title = $post['title'];
    }

    $issue->desc = array_key_exists ('desc', $post) ? $post['desc'] : $issue->desc;
    $issue->duedate = array_key_exists ('duedate', $post) ? $post['duedate'] : $issue->duedate;
    $issue->status = (isset ($post['status']) && in_array ($post['status'], [_ISSUE_STATUS_UNCHECK, _ISSUE_STATUS_CHECKED])) ? $post['status'] : $issue->status;
    $issue->save ();

    $issue = Model::Factory ('Issue')->find_one ($issue->id);

    return $app['json-success'] (200, $app['issueToLine'] ($issue));

  })->bind ('line/user/issue/update');


  /**
   *
   * Check Line user issue
   *
   */
  $app->put ('/line/user/{uid}/issue/{iid}/check', function (Request $request, $uid, $iid) use ($app) {

    // Check if user exists
    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_one ();

    if (! $user)
      return $app['json-error'] (400, 'User not exists');

    // Check if issue exists
    $issue = Model::Factory ('Issue')
      ->where ('id', $iid)
      ->find_one ();

    if (! $issue)
      return $app['json-error'] (400, 'Issue not exists');

    if ($issue->opener_id != $user->id && $issue->assignee_id != $user->id)
      return $app['json-error'] (400, 'Invalid operation');

    $issue->status = _ISSUE_STATUS_CHECKED;
    $issue->save ();

    return $app['json-success'] (200, $app['issueToLine'] ($issue));

  })->bind ('line/group/issue/check');


  /**
   *
   * Update Line group issue
   *
   */
  $app->put ('/line/user/{uid}/issue/{iid}/uncheck', function (Request $request, $uid, $iid) use ($app) {

    // Check if user exists
    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_one ();

    if (! $user)
      return $app['json-error'] (400, 'User not exists');

    // Check if issue exists
    $issue = Model::Factory ('Issue')
      ->where ('id', $iid)
      ->find_one ();

    if (! $issue)
      return $app['json-error'] (400, 'Issue not exists');

    if ($issue->opener_id != $user->id && $issue->assignee_id != $user->id)
      return $app['json-error'] (400, 'Invalid operation');

    $issue->status = _ISSUE_STATUS_UNCHECK;
    $issue->save ();

    return $app['json-success'] (200, $app['issueToLine'] ($issue));

  })->bind ('line/group/issue/uncheck');


  /**
   *
   * Delete Line group issue
   *
   */
  $app->delete ('/line/user/{uid}/issue/{iid}', function (Request $request, $uid, $iid) use ($app) {

    // Check if user exists
    $user = Model::Factory ('User')
      ->where ('line_id', $uid)
      ->find_one ();

    if (! $user)
      return $app['json-error'] (400, 'User not exists');

    // Check if issue exists
    $issue = Model::Factory ('Issue')
      ->where ('id', $iid)
      ->find_one ();

    if (! $issue)
      return $app['json-error'] (400, 'Issue not exists');

    if ($issue->opener_id != $user->id)
      return $app['json-error'] (400, 'Invalid operation');

    $issue->delete ();

    return $app['json-success'] (200, null);

  })->bind ('line/group/issue/delete');
