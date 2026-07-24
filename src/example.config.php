<?php
return array (
  'debug' => false,
  'installed' => false,
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
    'type' => 'sqlite',
    'host' => '127.0.0.1',
    'port' => 3306,
    'username' => '',
    'password' => '',
    'db' => 'todo',
    'charset' => 'utf8mb4',
  ),
  'session' => 
  array (
    'time' => 0,
    'session_name' => 'NovaSession',
  ),
  'login' => 
  array (
    'allowedLoginCount' => 1,
    'loginCallback' => '/',
    'logoutRedirect' => '/',
    'systemName' => 'Todo',
    'ssoEnable' => false,
  ),
);
