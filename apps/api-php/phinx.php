<?php
require __DIR__ . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();

return [
  'paths' => [
    'migrations' => __DIR__ . '/db/migrations',
    'seeds'      => __DIR__ . '/db/seeds',
  ],
  'environments' => [
    'default_environment' => 'production',
    'production' => [
      'adapter' => 'mysql',
      'host'    => $_ENV['DB_HOST'],
      'name'    => $_ENV['DB_NAME'],
      'user'    => $_ENV['DB_USER_MIGRATE'],
      'pass'    => $_ENV['DB_PASS_MIGRATE'],
      'port'    => 3306,
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci'
    ]
  ],
  'version_order' => 'execution'
];