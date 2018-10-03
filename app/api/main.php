<?php

  use Silex\Application;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\Response;
  use Symfony\Component\HttpKernel\HttpKernelInterface;
  use Pager\Wrapper\Paris\Pager;
  use Carbon\Carbon;


  /**
   *
   * Web index
   *
   */
  $app->get ('/', function (Request $request) use ($app) {

    return '<h1>Erukun API Server</h1>';

  })->bind ('home');
