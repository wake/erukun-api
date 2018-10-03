<?php


  /**
   *
   * DB bootstrap
   *
   */

  /**
   *
   * Paris & Idiorm
   *
   */
  $env = $app['env'];

  ORM::configure ('mysql:host='. $env['DB_HOST'] .';dbname='. $env['DB_NAME']);
  ORM::configure ('username', $env['DB_USER']);
  ORM::configure ('password', $env['DB_PASS']);

  ORM::configure ('logging',  $env['DB_LOGGING']);
  ORM::configure ('caching',  $env['DB_CACHING']);
  ORM::configure ('caching_auto_clear', $env['DB_CACHING_AUTO_CLEAR']);

  ORM::configure ('driver_options', array (PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '. $env['DB_CHARSET']));
