<?php

  use josegonzalez\Dotenv;

  if (! defined ('_ROOT')) {


    /**
     *
     * Root
     *
     */
    define ('_ROOT', dirname (__DIR__));


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
  }


  return [

    'paths' => [
      'migrations' => [
        '%%PHINX_CONFIG_DIR%%/../database/migrations',
      ],
      'seeds' => [
        '%%PHINX_CONFIG_DIR%%/../database/seeds',
      ],
    ],

    'environments' => [
      'default_migration_table' => 'migration',
      'default_database' => 'general',
      'general' => [
        'adapter' => 'mysql',
        'host' => $_ENV['DB_HOST'],
        'name' => $_ENV['DB_NAME'],
        'user' => $_ENV['DB_USER'],
        'pass' => $_ENV['DB_PASS'],
        'port' => $_ENV['DB_PORT'],
        'charset' => $_ENV['DB_CHARSET'],
        'collation' => $_ENV['DB_COLLATION'],
      ]
    ]
  ];
