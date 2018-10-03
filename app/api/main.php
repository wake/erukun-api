<?php

  use Silex\Application;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\Response;
  use Symfony\Component\HttpKernel\HttpKernelInterface;
  use Pager\Wrapper\Paris\Pager;
  use Carbon\Carbon;


  /**
   *
   * API web index
   *
   */
  $app->get ('/', function (Request $request) use ($app) {

    return '<h1>Erukun API Server</h1>';

  })->bind ('home');


  /**
   *
   * API error handler
   *
   */
  $app->error (function (\Exception $e, $code) use ($app) {

    return $app['json-error'] (404, 'API not exists or something is wrong');

  });
