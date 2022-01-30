<?php

$allServerCfg = [
    'auth_type'       => 'config',
    'AllowNoPassword' => true,
    'hide_db'         => 'information_schema',
    'ExecTimeLimit'   => 600,
];


# must start at index 1. Ref: https://docs.phpmyadmin.net/en/latest/config.html#server-connection-settings
$i=1;
$cfg['Servers'][$i++] = array_merge($allServerCfg, [
    'host'     => $_ENV['MYSQL_HOST'],
    'user'     => $_ENV['MYSQL_USER'],
    'password' => $_ENV['MYSQL_PASSWORD']
]);

$cfg['Servers'][$i++] = array_merge($allServerCfg, [
    'host'     => $_ENV['MARIADB_HOST'],
    'user'     => $_ENV['MARIADB_USER'],
    'password' => $_ENV['MARIADB_PASSWORD']
]);
