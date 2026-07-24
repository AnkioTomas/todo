<?php
return array (
  'debug' => true,
  'timezone' => 'Asia/Shanghai',
  'default_route' => true,
  'domain' => 
  array (
    0 => '0.0.0.0',
  ),
  'version' => '1.0.0',
  'ip' => '127.0.0.1',
  'port' => 10211,
  'framework_start' => 
  array (
    0 => 'nova\\plugin\\installer\\InstallerManager',
    1 => 'nova\\plugin\\login\\LoginManager',
    2 => 'nova\\plugin\\tpl\\Handler',
  ),
  'db' => 
  array (
    'type' => 'mysql',
    'host' => '',
    'port' => 3306,
    'username' => '',
    'password' => '',
    'db' => '',
    'charset' => 'utf8mb4',
  ),
);
