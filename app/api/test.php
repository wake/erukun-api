<?php

  use Silex\Application;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\Response;
  use Symfony\Component\HttpKernel\HttpKernelInterface;
  use Pager\Wrapper\Paris\Pager;
  use Carbon\Carbon;


  /**
   *
   * GET test
   *
   */
  $app->get ('/test', function (Request $request) use ($app) {

    return $app['json-success'] (200, null);

  })->bind ('test/get');


  /**
   *
   * POST test
   *
   */
  $app->post ('/test', function (Request $request) use ($app) {

    return $app['json-success'] (200, null);

  })->bind ('test/post');


  /**
   *
   * PUT test
   *
   */
  $app->put ('/test', function (Request $request) use ($app) {

    return $app['json-success'] (200, null);

  })->bind ('test/put');


  /**
   *
   * DELETE test
   *
   */
  $app->delete ('/test', function (Request $request) use ($app) {

    return $app['json-success'] (200, null);

  })->bind ('test/delete');
