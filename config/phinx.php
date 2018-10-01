<?php

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
      'host' => '%%PHINX_DB_HOST%%',
      'name' => '%%PHINX_DB_NAME%%',
      'user' => '%%PHINX_DB_USER%%',
      'pass' => '%%PHINX_DB_PASS%%',
      'port' => '%%PHINX_DB_PORT%%',
      'charset' => '%%PHINX_DB_CHARSET%%',
    ]
  ]
];
