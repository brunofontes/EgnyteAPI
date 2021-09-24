<?php
include 'Egnyte.php';
$config = parse_ini_file('config.ini', true);
$egnyte = new Egnyte();
$egnyte->setToken($config['egnyte_credentials']['token']);
//$token = $egnyte->getToken($config['egnyte_credentials']['id'], $config['egnyte_credentials']['secret'], $config['egnyte_credentials']['username'], $config['egnyte_credentials']['password']);
//print_r($token);
//print_r($egnyte->getUserInfo());
//print_r($egnyte->getUserPermission('bfontes', '/Shared/RIO/Projects'));
print_r($egnyte->createFolder('/Private/bfontes/ttteste/as- = _ d/fds/sdf/fds')); die();
print_r($egnyte->uploadFile('/run/media/bruno/Multimedia/Localização/Ccaps/TEMP/image.png', '/Private/bfontes/ttteste/image.png'));
//print_r($egnyte->createMultipleFolders(['/Private/bfontes/ttt/abc', '/Private/bfontes/ttt', '/Private/bfontes/aaa', '/Private/bfontes', '/Private/bfontes/']));
