<?php

  use Silex\Application;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\Response;
  use Symfony\Component\HttpKernel\HttpKernelInterface;
  use Symfony\Component\Yaml\Yaml;
  use Symfony\Component\Translation\Loader\PoFileLoader;
  use Symfony\Component\Translation\Loader\JsonFileLoader;
  use josegonzalez\Dotenv;


  /**
   *
   * Root
   *
   */
  define ('_ROOT', dirname (__DIR__));


  /**
   *
   * Resource (asset, component, core, external, model, view)
   *
   */
  define ('_RESOURCE', _ROOT . '/resource');


  /**
   *
   * Config
   *
   */
  define ('_CONFIG', _ROOT . '/config');


  /**
   *
   * Storage
   *
   */
  define ('_STORAGE', _ROOT . '/storage');


  /**
   *
   * Vendors
   *
   */
  require_once _ROOT . '/vendor/autoload.php';


  /**
   *
   * Environments and Defines
   *
   */
  if (! file_exists (_ROOT . '/.env'))
    throw new \Exception ('Environment file not exists, create one you own from file `.env.example`.');

  $dotenv = new Dotenv\Loader (_ROOT . '/.env');
  $dotenv->parse ()->toEnv ();

  defined ('_HOST') or define ('_HOST', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']);
  defined ('_BASE') or define ('_BASE', rtrim (dirname ($_SERVER['SCRIPT_NAME']), '/'));
  defined ('_HTTP') or define ('_HTTP', _HOST . _BASE);
  defined ('_URI')  or define ('_URI',  str_replace ('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));

  define ('_TIMESTAMP',  time ());
  define ('_DATE',       date ('Y-m-d', _TIMESTAMP));
  define ('_TIME',       date ('H:i:s', _TIMESTAMP));
  define ('_DATETIME',   _DATE .' '. _TIME);
  define ('_SECRET',     $_ENV['APP_SECRET']);


  /**
   *
   * Dynamic environment
   *
   */
  define ('_UPLOAD', _ROOT . '/public' . $_ENV['SITE_UPLOAD_PATH']);
  define ('_UPLOAD_BASE', ltrim ($_ENV['SITE_UPLOAD_PATH'], '/'));


  /**
   *
   * Site common setting
   *
   */
  define ('_SITE_NAME', isset ($_ENV['SITE_NAME']) ? $_ENV['SITE_NAME'] : '');
  define ('_SITE_URL', (isset ($_ENV['SITE_URL']) && $_ENV['SITE_URL'] != '') ? $_ENV['SITE_URL'] : '');


  /**
   *
   * Initial application
   *
   */

  class _Application extends Silex\Application {

    use Silex\Application\UrlGeneratorTrait;
    use Silex\Application\FormTrait;
    use Silex\Application\SwiftmailerTrait;
    use Silex\Application\TranslationTrait;
    use Silex\Application\SecurityTrait;

    use Silex\Application\FileLoaderTrait;
    use Silex\Application\TwigHelperTrait;
    use Silex\Application\MobileDetectTrait;
  }

  $app = new _Application ();


  /**
   *
   * Application evironments & config
   *
   */
  $app['env'] = $_ENV;


  /**
   *
   * Application CSRF protection
   *
   */
  $app['csrf'] = bin2hex (random_bytes (18));


  /**
   *
   * Debug mode
   *
   */
  $app['debug'] = false;

  if (isset ($_ENV['APP_DEBUG_ENABLE']) && $_ENV['APP_DEBUG_ENABLE'] == true) {

    if (isset ($_GET[$_ENV['APP_DEBUG_KEYWORD']]))
      $app['debug'] = true;

    if (isset ($_ENV['APP_DEBUG_FORCE']) && $_ENV['APP_DEBUG_FORCE'] == true)
      $app['debug'] = true;
  }


  /**
   *
   * Register services
   *
   */

  // Struct Loader Provider
  $app->register (new Silex\Provider\FileLoaderProvider ());

  // Seervice Controller Provider
  $app->register (new Silex\Provider\ServiceControllerServiceProvider ());

  // Validator
  $app->register (new Silex\Provider\ValidatorServiceProvider ());

  // HTTP Fragment
  $app->register (new Silex\Provider\HttpFragmentServiceProvider ());

  // Form
  $app->register (new Silex\Provider\FormServiceProvider ());

  // Locale (Needed by translation service)
  $app->register (new Silex\Provider\LocaleServiceProvider());

  // Translation (Needed by form service)
  $app->register (new Silex\Provider\TranslationServiceProvider ());

  // Session
  $app->register(new Silex\Provider\SessionServiceProvider (), [
    'session.storage.options' => [
      'cookie_lifetime' => 31536000,
      'gc_maxlifetime' => 31536000,
      'gc_probability' => 0
    ]
  ]);

  $app['session']->registerBag (new Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag ());
