<?php

  use Silex\Application;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\Response;
  use Symfony\Component\HttpKernel\HttpKernelInterface;
  use Symfony\Component\Yaml\Yaml;


  /**
   *
   * App Bootstrap (load manually)
   *
   */
  require_once dirname (__DIR__) . '/bootstrap/app.php';


  /**
   *
   * Service bootstrap
   *
   */
  $app->autoload ([], _ROOT . '/config');


  /**
   *
   * Enable profiler under debug mode
   *
   */
  if ($app['debug'] == true) {

    // Profilter
    $app->register (new Silex\Provider\WebProfilerServiceProvider (), [
      'profiler.cache_dir'    => _STORAGE . '/profiler/caches',
      'profiler.mount_prefix' => '/_profiler',
    ]);
  }


  /**
   *
   * Json return shortcut (success)
   *
   */
  $app['json-success'] = $app->protect (function ($state, $context) use ($app) {
    return $app->json ([

      'state' => [
        'code' => 1,
        'message' => ''
      ],

      'result' => $context

    ], $state, array ('Content-Type' => 'application/json'));
  });


  /**
   *
   * Json return shortcut (error)
   *
   */
  $app['json-error'] = $app->protect (function ($state, $message) use ($app) {
    return $app->json (['code' => -1, 'message' => $message], $state, array ('Content-Type' => 'application/json'));
  });


  /**
   *
   * Boot Application
   *
   */
  $app->boot ();


  /**
   *
   * Load models
   *
   */
  $app->autoload (_RESOURCE . '/model');


  /**
   *
   * Load Web App
   *
   */
  $app->autoload (_ROOT . '/app/api');


  /**
   *
   * Execute
   *
   */
  $app->run ();
