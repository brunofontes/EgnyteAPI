<?php
include 'Egnyte.php';
$config = parse_ini_file('config.ini', true);
$egnyte = new Egnyte();
$egnyte->setToken($config['egnyte_credentials']['token']);
// $token = $egnyte->getToken($config['egnyte_credentials']['id'], $config['egnyte_credentials']['username'], $config['egnyte_credentials']['password']);
// print_r($egnyte->getUserInfo());
// print_r($egnyte->getUserPermission('bfontes', '/Shared/RIO/Projects'));
print_r($egnyte->createFolder('/Private/bfontes/ttt'));
