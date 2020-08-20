<?php

// 数据库配置  注意如果存在多个数据库链接
// 第一个字段必须以db为首
// 第二个字段是数据库名称
$config['db']['hardware'] = [
    'host'=>'127.0.0.1',
    'username'=>'root',
    'password'=>'123456',
    'dbname'=>'hardware'
];

// 默认控制器和操作名
$config['defaultController'] = 'Item';
$config['defaultAction'] = 'index';

//redis配置 redis 存在多个链接 只需要以第一个字段做区分

$config['redis'] = [
    'hostname'=>'127.0.0.1',
    'port'=>6379,
    'password'=>'123456',
    'database'=>1
];



return $config;
